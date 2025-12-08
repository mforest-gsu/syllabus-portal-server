<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;

class CvRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private S3Client $s3Client,
        private SerializerInterface $serializer,
        private string $bucket,
        private string $prefix
    ) {
        // empty
    }


    public function getDownloadUrl(
        CourseSection $courseSection,
        int $expiresIn
    ): string|null {
        return $courseSection->hasCv()
            ? $this->s3Client->createPresignedRequest(
                $this->s3Client->getCommand('GetObject', [
                    'Bucket' => $this->bucket,
                    'Key' => $this->getObjectKey($courseSection),
                    "ResponseContentDisposition" => 'inline',
                    'ResponseContentType' => 'application/pdf'
                ]),
                "+{$expiresIn} seconds"
            )->getUri()->__toString()
            : null;
    }


    public function getCv(string $instructorPidm): CourseSection|null
    {
        $courseSectionClass = CourseSection::class;
        $dql = "
            SELECT
                CourseSection
            FROM
                {$courseSectionClass} CourseSection
            WHERE
                CourseSection.instructorPidm = :instructorPidm AND
                CourseSection.cvStatus = 'Complete'
        ";
        $params = [
            ':instructorPidm' => $instructorPidm
        ];

        /** @var CourseSection[] $data */
        $data = $this->entityManager
            ->createQuery($dql)
            ->setParameters($params)
            ->getResult();

        foreach ($data as $cv) {
            return $cv;
        }

        return null;
    }


    public function addCv(
        CourseSection $courseSection,
        User $user,
        string $filePath
    ): void {
        if (!$courseSection->hasInstructor()) {
            return;
        }

        $fileExt = match (mime_content_type($filePath)) {
            "application/pdf" => "pdf",
            default => throw new \RuntimeException()
        };

        $cvKey = $this->getObjectKey($courseSection, $fileExt);

        try {
            $f = fopen($filePath, "r");
            if (!is_resource($f)) {
                throw new \RuntimeException();
            }

            $this->s3Client->upload($this->bucket, $cvKey, $f);
        } finally {
            if (is_resource($f ?? null)) {
                fclose($f);
            }
            unset($f);
        }

        $this->updateCv(
            $courseSection->instructorPidm,
            $cvKey,
            $fileExt,
            $user->getPreferredUsername(),
            new \DateTime()
        );
    }


    public function removeCv(CourseSection $courseSection): void
    {
        // Remove file
        if ($courseSection->hasSyllabus()) {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getObjectKey($courseSection, $courseSection->syllabusExtension)
            ]);
        }

        $this->updateCv(
            $courseSection->instructorPidm,
            null,
            null,
            null,
            null
        );
    }


    public function updateCv(
        string $instructorPidm,
        string|null $key,
        string|null $fileExt,
        string|null $uploadedBy,
        \DateTime|null $uploadedOn
    ): void {
        if (is_string($key)) {
            $status = "Complete";
            $fileExt = $fileExt ?? throw new \RuntimeException();
            $uploadedBy = $uploadedBy ?? throw new \RuntimeException();
            $uploadedOn = $uploadedOn ?? throw new \RuntimeException();
        } else {
            $status = "Pending";
            $fileExt = null;
            $uploadedBy = null;
            $uploadedOn = null;
        }

        $courseSectionClass = CourseSection::class;
        $dql = "
            SELECT
                a
            FROM
                {$courseSectionClass} a
            WHERE
                a.instructorPidm = :instructorPidm
        ";

        /** @var CourseSection[] $data */
        $data = $this->entityManager
            ->createQuery($dql)
            ->setParameters([':instructorPidm' => $instructorPidm])
            ->getResult();

        foreach ($data as $cvSection) {
            if (
                $status !== $cvSection->cvStatus
                || $key !== $cvSection->cvKey
                || $fileExt !== $cvSection->cvExtension
                || $uploadedBy !== $cvSection->cvUploadedBy
                || $uploadedOn?->getTimestamp() !== $cvSection->cvUploadedOn?->getTimestamp()
            ) {
                $cvSection->cvStatus = $status;
                $cvSection->cvKey = $key;
                $cvSection->cvExtension = $fileExt;
                $cvSection->cvUploadedBy = $uploadedBy;
                $cvSection->cvUploadedOn = $uploadedOn;

                $this->uploadMetadata($cvSection);
            }
        }

        $this->entityManager->flush();
    }


    public function updateWithCv(CourseSection $currentSection): CourseSection
    {
        $cv = $currentSection->hasInstructor() ? $this->getCv($currentSection->instructorPidm) : null;
        if ($cv !== null) {
            $currentSection->cvStatus = $cv->cvStatus;
            $currentSection->cvKey = $cv->cvKey;
            $currentSection->cvExtension = $cv->cvExtension;
            $currentSection->cvUploadedBy = $cv->cvUploadedBy;
            $currentSection->cvUploadedOn = $cv->cvUploadedOn;
        } else {
            $currentSection->cvStatus = "Pending";
            $currentSection->cvKey = null;
            $currentSection->cvExtension = null;
            $currentSection->cvUploadedBy = null;
            $currentSection->cvUploadedOn = null;
        }

        return $currentSection;
    }


    public function uploadMetadata(CourseSection $courseSection): void
    {
        $this->s3Client->upload(
            $this->bucket,
            sprintf(
                '%s/%s/%s.json',
                $this->prefix,
                $courseSection->termCode,
                $courseSection->crn
            ),
            $this->serializer->serialize($courseSection, 'json')
        );
    }


    private function getObjectKey(
        CourseSection $courseSection,
        string|null $cvExtension = null
    ): string {
        return sprintf(
            '%s/cv/%s.%s',
            $this->prefix,
            $courseSection->instructorId,
            $cvExtension ?? $courseSection->cvExtension
        );
    }
}
