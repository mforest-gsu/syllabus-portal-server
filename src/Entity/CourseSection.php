<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Symfony\Bridge\Doctrine\Types\UuidType;

#[ORM\Entity(CourseSectionRepository::class)]
#[ORM\UniqueConstraint(
    name: 'course_section_term_crn',
    columns: [
        'term_code',
        'crn'
    ]
)]
#[ORM\UniqueConstraint(
    name: 'course_section_term_crn',
    columns: [
        'term_code',
        'subject_code',
        'course_number',
        'course_sequence',
    ]
)]
class CourseSection implements \JsonSerializable, \Stringable
{
    /**
     * @param string $id
     * @param string $termCode
     * @param string $termName
     * @param string $crn
     * @param string $collegeCode
     * @param string $collegeName
     * @param string $departmentCode
     * @param string $departmentName
     * @param string $campusCode
     * @param string $campusName
     * @param string $courseEffectiveTerm
     * @param string $subjectCode
     * @param string $courseNumber
     * @param string $courseSequence
     * @param string $courseTitle
     * @param string $instructorPidm
     * @param string $instructorId
     * @param string|null $instructorFirstName
     * @param string|null $instructorLastName
     * @param string|null $instructorEmail
     * @param string $syllabusStatus
     * @param string|null $syllabusKey
     * @param DateTime|null $syllabusUploadedOn
     * @param string|null $syllabusUploadedBy
     * @param bool $active
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 12)]
        public string $id = '',
        #[ORM\Column(type: Types::STRING, length: 6)]
        public string $termCode = '',
        #[ORM\Column(type: Types::STRING, length: 30)]
        public string $termName = '',
        #[ORM\Column(type: Types::STRING, length: 5)]
        public string $crn = '',
        #[ORM\Column(type: Types::STRING, length: 2)]
        public string $collegeCode = '',
        #[ORM\Column(type: Types::STRING, length: 100)]
        public string $collegeName = '',
        #[ORM\Column(type: Types::STRING, length: 4)]
        public string $departmentCode = '',
        #[ORM\Column(type: Types::STRING, length: 30)]
        public string $departmentName = '',
        #[ORM\Column(type: Types::STRING, length: 3)]
        public string $campusCode = '',
        #[ORM\Column(type: Types::STRING, length: 30)]
        public string $campusName = '',
        #[ORM\Column(type: Types::STRING, length: 6)]
        public string $courseEffectiveTerm = '',
        #[ORM\Column(type: Types::STRING, length: 4)]
        public string $subjectCode = '',
        #[ORM\Column(type: Types::STRING, length: 5)]
        public string $courseNumber = '',
        #[ORM\Column(type: Types::STRING, length: 3)]
        public string $courseSequence = '',
        #[ORM\Column(type: Types::STRING, length: 30)]
        public string $courseTitle = '',
        #[ORM\Column(type: Types::STRING, length: 8)]
        public string $instructorPidm = '00000000',
        #[ORM\Column(type: Types::STRING, length: 9)]
        public string $instructorId = 'STAFF',
        #[ORM\Column(type: Types::STRING, length: 60, nullable: true)]
        public string|null $instructorFirstName = 'Staff',
        #[ORM\Column(type: Types::STRING, length: 60, nullable: true)]
        public string|null $instructorLastName = 'Instructor',
        #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
        public string|null $instructorEmail = null,
        #[ORM\Column(type: Types::STRING, length: 10)]
        public string $syllabusStatus = 'Pending',
        public string|null $syllabusUrl = null,
        #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
        public string|null $syllabusKey = null,
        #[ORM\Column(type: Types::STRING, length: 16, nullable: true)]
        public string|null $syllabusExtension = null,
        #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
        public \DateTime|null $syllabusUploadedOn = null,
        #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
        public string|null $syllabusUploadedBy = null,
        #[ORM\Column(type: Types::BOOLEAN)]
        public bool $active = true
    ) {
    }

    public function init(): static
    {
        $this->id = implode('_', [
            $this->termCode,
            $this->crn
        ]);

        $this->active = true;

        if (!$this->hasInstructor()) {
            $this->instructorPidm = "0";
            $this->instructorId = "STAFF";
            $this->instructorFirstName = "Staff";
            $this->instructorLastName = "Instructor";
            $this->instructorEmail = null;
        }

        return $this;
    }

    public function hasInstructor(): bool
    {
        return !in_array($this->instructorPidm, ['','0','00000000'], true);
    }

    public function hasSyllabus(): bool
    {
        return $this->hasInstructor() && $this->syllabusStatus === 'Complete' && $this->syllabusExtension !== null;
    }

    public function getObjectKey(
        string $prefix,
        string|null $syllabusExtension = null
    ): string {
        return sprintf(
            '%s/%s/%s.%s',
            $prefix,
            $this->termCode,
            $this->crn,
            $syllabusExtension ?? $this->syllabusExtension
        );
    }

    /**
     * @param CourseSection $source
     * @param bool $everything
     * @return bool
     */
    public function setValues(
        CourseSection $source,
        bool $everything = false
    ): bool {
        if ($source->getChecksum() === $this->getChecksum()) {
            return false;
        }

        $this->termCode = $source->termCode;
        $this->termName = $source->termName;
        $this->crn = $source->crn;
        $this->collegeCode = $source->collegeCode;
        $this->collegeName = $source->collegeName;
        $this->departmentCode = $source->departmentCode;
        $this->departmentName = $source->departmentName;
        $this->campusCode = $source->campusCode;
        $this->campusName = $source->campusName;
        $this->courseEffectiveTerm = $source->courseEffectiveTerm;
        $this->subjectCode = $source->subjectCode;
        $this->courseNumber = $source->courseNumber;
        $this->courseSequence = $source->courseSequence;
        $this->courseTitle = $source->courseTitle;
        $this->instructorPidm = $source->instructorPidm;
        $this->instructorId = $source->instructorId;
        $this->instructorFirstName = $source->instructorFirstName;
        $this->instructorLastName = $source->instructorLastName;
        $this->instructorEmail = $source->instructorEmail;

        if ($everything) {
            $this->id = $source->id;
            $this->syllabusStatus = $source->syllabusStatus;
            $this->syllabusKey = $source->syllabusKey;
            $this->syllabusUrl = $source->syllabusUrl;
            $this->syllabusExtension = $source->syllabusExtension;
            $this->syllabusUploadedBy = $source->syllabusUploadedBy;
            $this->syllabusUploadedOn = $source->syllabusUploadedOn;
            $this->active = $source->active;
        }

        return true;
    }


    /**
     * @param bool $everything
     * @return array<string,mixed>
     */
    public function getValues(bool $everything = false): array
    {
        $values = $everything
            ? get_object_vars($this)
            : array_filter(
                get_object_vars($this),
                fn(string $k): bool => (
                    $k !== 'id' &&
                    $k !== 'active' &&
                    !str_starts_with($k, 'syllabus')
                ),
                ARRAY_FILTER_USE_KEY
            );

        ksort($values);

        return $values;
    }


    /**
     * @param bool $everything
     * @return string
     */
    public function getChecksum(bool $everything = false): string
    {
        return hash(
            'SHA1',
            json_encode(
                $this->getValues($everything),
                JSON_THROW_ON_ERROR
            )
        );
    }


    public function jsonSerialize(): mixed
    {
        return $this->getValues(true);
    }


    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }


    public function hashCode(): string
    {
        return hash(
            'SHA256',
            json_encode([static::class, $this], JSON_THROW_ON_ERROR)
        );
    }
}
