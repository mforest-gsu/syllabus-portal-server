<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

final class CourseSectionQueryParams
{
    public function __construct(
        string $termCode = "",
        string $collegeCode = "",
        string $departmentCode = "",
        string|null $campusCode = null,
        string|null $subjectCode = null,
        string|null $courseNumber = null,
        string|null $courseTitle = null,
        string|null $crn = null,
        string|null $instructorId = null,
        string|null $instructorFirstName = null,
        string|null $instructorLastName = null,
        string|null $instructorEmail = null,
        string|null $syllabusIsRequired = null,
        string|null $syllabusStatus = null,
        string|null $syllabusUploadedBy = null,
        string|null $syllabusUploadedOnStart = null,
        string|null $syllabusUploadedOnEnd = null,
        string|null $cvStatus = null,
        string|null $cvUploadedBy = null,
        string|null $cvUploadedOnStart = null,
        string|null $cvUploadedOnEnd = null,
        int $offset = 0,
        int $limit = 25,
        string|null $orderBy = null
    ) {
        $this
            ->setTermCode($termCode)
            ->setCollegeCode($collegeCode)
            ->setDepartmentCode($departmentCode)
            ->setCampusCode($campusCode)
            ->setSubjectCode($subjectCode)
            ->setCourseNumber($courseNumber)
            ->setCourseTitle($courseTitle)
            ->setCrn($crn)
            ->setInstructorId($instructorId)
            ->setInstructorFirstName($instructorFirstName)
            ->setInstructorLastName($instructorLastName)
            ->setInstructorEmail($instructorEmail)
            ->setSyllabusIsRequired($syllabusIsRequired)
            ->setSyllabusStatus($syllabusStatus)
            ->setSyllabusUploadedBy($syllabusUploadedBy)
            ->setSyllabusUploadedOnStart($syllabusUploadedOnStart)
            ->setSyllabusUploadedOnEnd($syllabusUploadedOnEnd)
            ->setCvStatus($cvStatus)
            ->setCvUploadedBy($cvUploadedBy)
            ->setCvUploadedOnStart($cvUploadedOnStart)
            ->setCvUploadedOnEnd($cvUploadedOnEnd)
            ->setOffset($offset)
            ->setLimit($limit)
            ->setOrderBy($orderBy);
    }


    private string $termCode = "";
    public function getTermCode(): string
    {
        return $this->termCode;
    }
    public function setTermCode(string $termCode): static
    {
        $this->termCode = $termCode;
        return $this;
    }


    private string $collegeCode = "";
    public function getCollegeCode(): string
    {
        return $this->collegeCode;
    }
    public function setCollegeCode(string $collegeCode): static
    {
        $this->collegeCode = $collegeCode;
        return $this;
    }


    private string $departmentCode = "";
    public function getDepartmentCode(): string
    {
        return $this->departmentCode;
    }
    public function setDepartmentCode(string $departmentCode): static
    {
        $this->departmentCode = $departmentCode;
        return $this;
    }


    private string|null $campusCode = null;
    public function getCampusCode(): string|null
    {
        return $this->campusCode;
    }
    public function setCampusCode(string|null $campusCode): static
    {
        $this->campusCode = $campusCode;
        return $this;
    }


    private string|null $subjectCode = null;
    public function getSubjectCode(): string|null
    {
        return $this->subjectCode;
    }
    public function setSubjectCode(string|null $subjectCode): static
    {
        $this->subjectCode = $subjectCode;
        return $this;
    }


    private string|null $courseNumber = null;
    public function getCourseNumber(): string|null
    {
        return $this->courseNumber;
    }
    public function setCourseNumber(string|null $courseNumber): static
    {
        $this->courseNumber = $courseNumber;
        return $this;
    }


    private string|null $courseTitle = null;
    public function getCourseTitle(): string|null
    {
        return $this->courseTitle;
    }
    public function setCourseTitle(string|null $courseTitle): static
    {
        $this->courseTitle = $courseTitle;
        return $this;
    }


    private string|null $crn = null;
    public function getCrn(): string|null
    {
        return $this->crn;
    }
    public function setCrn(string|null $crn): static
    {
        $this->crn = $crn;
        return $this;
    }


    private string|null $instructorId = null;
    public function getInstructorId(): string|null
    {
        return $this->instructorId;
    }
    public function setInstructorId(string|null $instructorId): static
    {
        $this->instructorId = $instructorId;
        return $this;
    }


    private string|null $instructorFirstName = null;
    public function getInstructorFirstName(): string|null
    {
        return $this->instructorFirstName;
    }
    public function setInstructorFirstName(string|null $instructorFirstName): static
    {
        $this->instructorFirstName = $instructorFirstName;
        return $this;
    }


    private string|null $instructorLastName = null;
    public function getInstructorLastName(): string|null
    {
        return $this->instructorLastName;
    }
    public function setInstructorLastName(string|null $instructorLastName): static
    {
        $this->instructorLastName = $instructorLastName;
        return $this;
    }


    private string|null $instructorEmail = null;
    public function getInstructorEmail(): string|null
    {
        return $this->instructorEmail;
    }
    public function setInstructorEmail(string|null $instructorEmail): static
    {
        $this->instructorEmail = $instructorEmail;
        return $this;
    }


    private string|null $syllabusIsRequired = null;
    public function getSyllabusIsRequired(): string|null
    {
        return $this->syllabusIsRequired;
    }
    public function setSyllabusIsRequired(string|null $syllabusIsRequired): static
    {
        $this->syllabusIsRequired = $syllabusIsRequired;
        return $this;
    }


    private string|null $syllabusStatus = null;
    public function getSyllabusStatus(): string|null
    {
        return $this->syllabusStatus;
    }
    public function setSyllabusStatus(string|null $syllabusStatus): static
    {
        $this->syllabusStatus = $syllabusStatus;
        return $this;
    }


    private string|null $syllabusUploadedBy = null;
    public function getSyllabusUploadedBy(): string|null
    {
        return $this->syllabusUploadedBy;
    }
    public function setSyllabusUploadedBy(string|null $syllabusUploadedBy): static
    {
        $this->syllabusUploadedBy = $syllabusUploadedBy;
        return $this;
    }


    private string|null $syllabusUploadedOnStart = null;
    public function getSyllabusUploadedOnStart(): string|null
    {
        return $this->syllabusUploadedOnStart;
    }
    public function setSyllabusUploadedOnStart(string|null $syllabusUploadedOnStart): static
    {
        $this->syllabusUploadedOnStart = $syllabusUploadedOnStart;
        return $this;
    }


    private string|null $syllabusUploadedOnEnd = null;
    public function getSyllabusUploadedOnEnd(): string|null
    {
        return $this->syllabusUploadedOnEnd;
    }
    public function setSyllabusUploadedOnEnd(string|null $syllabusUploadedOnEnd): static
    {
        $this->syllabusUploadedOnEnd = $syllabusUploadedOnEnd;
        return $this;
    }


    private string|null $cvStatus = null;
    public function getCvStatus(): string|null
    {
        return $this->cvStatus;
    }
    public function setCvStatus(string|null $cvStatus): static
    {
        $this->cvStatus = $cvStatus;
        return $this;
    }


    private string|null $cvUploadedBy = null;
    public function getCvUploadedBy(): string|null
    {
        return $this->cvUploadedBy;
    }
    public function setCvUploadedBy(string|null $cvUploadedBy): static
    {
        $this->cvUploadedBy = $cvUploadedBy;
        return $this;
    }


    private string|null $cvUploadedOnStart = null;
    public function getCvUploadedOnStart(): string|null
    {
        return $this->cvUploadedOnStart;
    }
    public function setCvUploadedOnStart(string|null $cvUploadedOnStart): static
    {
        $this->cvUploadedOnStart = $cvUploadedOnStart;
        return $this;
    }


    private string|null $cvUploadedOnEnd = null;
    public function getCvUploadedOnEnd(): string|null
    {
        return $this->cvUploadedOnEnd;
    }
    public function setCvUploadedOnEnd(string|null $cvUploadedOnEnd): static
    {
        $this->cvUploadedOnEnd = $cvUploadedOnEnd;
        return $this;
    }


    private int $offset = 0;
    public function getOffset(): int
    {
        return $this->offset;
    }
    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }


    private int $limit = 25;
    public function getLimit(): int
    {
        return $this->limit;
    }
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }


    private string|null $orderBy = null;
    public function getOrderBy(): string|null
    {
        return $this->orderBy;
    }
    public function setOrderBy(string|null $orderBy): static
    {
        $this->orderBy = $orderBy;
        return $this;
    }


    /**
     * @param list<string> $requiredParams
     * @param list<string> $skipParams
     * @return array<string,string>
     */
    public function getParamValues(
        array $requiredParams = [],
        array $skipParams = []
    ): array {
        /** @var array<string,mixed> $objectVars */
        $objectVars = get_object_vars($this);

        /** @var array<string,string> $stringVars */
        $stringVars = array_map(
            strval(...),
            array_filter(
                $objectVars,
                fn(mixed $value, string $key): bool => (
                    is_scalar($value)
                    && !in_array($key, $skipParams, true)
                ),
                ARRAY_FILTER_USE_BOTH
            )
        );

        return array_filter(
            $stringVars,
            fn(string $value, string $key): bool => (
                in_array($key, $requiredParams, true) ||
                $value !== ""
            ),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
