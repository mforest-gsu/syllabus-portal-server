<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Aws\S3\S3Client;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;

class SyllabusRepository
{
    public function __construct(
        private S3Client $s3Client,
        private SerializerInterface $serializer,
        private string $bucket = 'shared-data-qa',
        private string $prefix = 'cetl'
    ) {
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
        if (!$courseSection->hasInstructor()) {
            throw new \RuntimeException();
        }

        $fileExt = match (mime_content_type($filePath)) {
            "application/pdf" => "pdf",
            default => throw new \RuntimeException()
        };

        $courseSection->syllabusStatus = "Complete";
        $courseSection->syllabusKey = $this->getObjectKey($courseSection, $fileExt);
        $courseSection->syllabusExtension = $fileExt;
        $courseSection->syllabusUploadedBy = $user->getPreferredUsername();
        $courseSection->syllabusUploadedOn = new \DateTime();

        try {
            $f = fopen($filePath, "r");
            if (!is_resource($f)) {
                throw new \RuntimeException();
            }
            $this->addObject(
                $this->getObjectKey($courseSection, 'json'),
                $this->serializer->serialize($courseSection, 'json')
            );
            $this->addObject(
                $this->getObjectKey($courseSection),
                $f
            );
        } finally {
            if (is_resource($f ?? null)) {
                fclose($f);
            }
        }
    }


    public function addSyllabusMetadata(CourseSection $courseSection): void
    {
        if ($courseSection->hasInstructor()) {
            throw new \RuntimeException();
        }

        $this->addObject(
            $this->getObjectKey($courseSection, 'json'),
            $this->serializer->serialize($courseSection, 'json')
        );
    }


    public function removeSyllabus(CourseSection $courseSection): void
    {
        if (!$courseSection->hasSyllabus()) {
            throw new \RuntimeException();
        }

        $this->removeObject($this->getObjectKey($courseSection, 'json'));
        $this->removeObject($this->getObjectKey($courseSection));

        $courseSection->syllabusStatus = "Pending";
        $courseSection->syllabusKey = null;
        $courseSection->syllabusExtension = null;
        $courseSection->syllabusUploadedBy = null;
        $courseSection->syllabusUploadedOn = null;
    }


    public function removeSyllabusMetadata(CourseSection $courseSection): void
    {
        if ($courseSection->hasInstructor()) {
            throw new \RuntimeException();
        }

        $this->removeObject($this->getObjectKey($courseSection, 'json'));
    }


    private function getObjectKey(
        CourseSection $courseSection,
        string|null $syllabusExtension = null
    ): string {
        return $courseSection->getObjectKey($this->prefix, $syllabusExtension);
    }


    private function addObject(
        string $key,
        mixed $content
    ): void {
        $this->s3Client->upload(
            $this->bucket,
            $key,
            $content
        );
    }


    private function removeObject(string $key): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);
    }
}
