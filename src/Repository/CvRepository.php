<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Aws\S3\S3Client;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;

class CvRepository
{
    public function __construct(
        private S3Client $s3Client,
        private string $bucket,
        private string $prefix
    ) {
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


    public function addCv(
        CourseSection $courseSection,
        User $user,
        string $filePath
    ): void {
        $fileExt = match (mime_content_type($filePath)) {
            "application/pdf" => "pdf",
            default => throw new \RuntimeException()
        };

        $courseSection->cvStatus = "Complete";
        $courseSection->cvKey = $this->getObjectKey($courseSection, $fileExt);
        $courseSection->cvExtension = $fileExt;
        $courseSection->cvUploadedBy = $user->getPreferredUsername();
        $courseSection->cvUploadedOn = new \DateTime();

        try {
            $f = fopen($filePath, "r");
            if (!is_resource($f)) {
                throw new \RuntimeException();
            }
            // $this->addObject(
            //     $this->getObjectKey($courseSection, 'json'),
            //     $this->serializer->serialize($courseSection, 'json')
            // );
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


    public function removeCv(CourseSection $courseSection): void
    {
        if ($courseSection->hasCv()) {
            $this->removeObject($this->getObjectKey($courseSection));
            $this->removeObject($this->getObjectKey($courseSection, 'json'));
        }

        $courseSection->cvStatus = "Pending";
        $courseSection->cvUrl = null;
        $courseSection->cvKey = null;
        $courseSection->cvExtension = null;
        $courseSection->cvUploadedBy = null;
        $courseSection->cvUploadedOn = null;
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
