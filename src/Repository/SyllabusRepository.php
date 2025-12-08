<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;

class SyllabusRepository
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
        return $courseSection->hasSyllabus()
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


    public function addSyllabus(
        CourseSection $courseSection,
        User $user,
        string $filePath
    ): void {
        $fileExt = match (mime_content_type($filePath)) {
            "application/pdf" => "pdf",
            default => throw new \RuntimeException()
        };

        $syllabusKey = $this->getObjectKey($courseSection, $fileExt);

        try {
            $f = fopen($filePath, "r");
            if (!is_resource($f)) {
                throw new \RuntimeException();
            }

            $this->s3Client->upload($this->bucket, $syllabusKey, $f);
        } finally {
            if (is_resource($f ?? null)) {
                fclose($f);
            }

            unset($f);
        }

        $this->updateSyllabus(
            $courseSection,
            $syllabusKey,
            $fileExt,
            $user->getPreferredUsername(),
            new \DateTime()
        );
    }


    public function removeSyllabus(CourseSection $courseSection): void
    {
        // Remove file
        if ($courseSection->hasSyllabus()) {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getObjectKey(
                    $courseSection,
                    $courseSection->syllabusExtension
                )
            ]);
        }

        $this->updateSyllabus($courseSection, null, null, null, null);
    }


    public function updateSyllabus(
        CourseSection $courseSection,
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

        // Update record
        $courseSection->syllabusStatus = $status;
        $courseSection->syllabusKey = $key;
        $courseSection->syllabusExtension = $fileExt;
        $courseSection->syllabusUploadedBy = $uploadedBy;
        $courseSection->syllabusUploadedOn = $uploadedOn;

        $this->uploadMetadata($courseSection);

        $this->entityManager->flush();
    }


    public function uploadMetadata(CourseSection $courseSection): void
    {
        $this->s3Client->upload(
            $this->bucket,
            $this->getObjectKey($courseSection, 'json'),
            $this->serializer->serialize($courseSection, 'json')
        );
    }


    public function removeMetadata(CourseSection $courseSection): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getObjectKey($courseSection, 'json')
        ]);
    }


    private function getObjectKey(
        CourseSection $courseSection,
        string|null $syllabusExtension = null
    ): string {
        return sprintf(
            '%s/%s/%s.%s',
            $this->prefix,
            $courseSection->termCode,
            $courseSection->crn,
            $syllabusExtension ?? $courseSection->syllabusExtension
        );
    }
}
