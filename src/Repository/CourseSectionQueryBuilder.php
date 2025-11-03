<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Gsu\SyllabusPortal\Entity\CourseSection;

class CourseSectionQueryBuilder
{
    /** @var string $entityName */
    private string $entityName;
    /** @var list<string> $requiredParams */
    private array $requiredParams;
    /** @var list<string> $skipParams */
    private array $skipParams;
    /** @var array<string,string> $whereFragments */
    private array $whereFragments;


    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
        $this->entityName = CourseSection::class;
        $this->requiredParams = ['termCode','collegeCode','departmentCode'];
        $this->skipParams = ['offset', 'limit', 'orderBy'];
        $this->whereFragments = [
            ":termCode" => "CourseSection.termCode = :termCode",
            ":collegeCode" => "CourseSection.collegeCode = :collegeCode",
            ":departmentCode" => "CourseSection.departmentCode = :departmentCode",
            ":campusCode" => "CourseSection.campusCode = :campusCode",
            ":subjectCode" => "CourseSection.subjectCode LIKE :subjectCode",
            ":courseNumber" => "CourseSection.courseNumber LIKE :courseNumber",
            ":courseTitle" => "CourseSection.courseTitle LIKE :courseTitle",
            ":crn" => "CourseSection.crn = :crn",
            ":instructorId" => "CourseSection.instructorId = :instructorId",
            ":instructorFirstName" => "CourseSection.instructorFirstName LIKE :instructorFirstName",
            ":instructorLastName" => "CourseSection.instructorLastName LIKE :instructorLastName",
            ":instructorEmail" => "CourseSection.instructorEmail LIKE :instructorEmail",
            ":syllabusIsRequired" => "CourseSection.syllabusIsRequired = :syllabusIsRequired",
            ":syllabusStatus" => "CourseSection.syllabusStatus = :syllabusStatus",
            ":syllabusUploadedBy" => "CourseSection.syllabusUploadedBy LIKE :syllabusUploadedBy",
            ":syllabusUploadedOnStart" => "CourseSection.syllabusUploadedOn >= :syllabusUploadedOnStart",
            ":syllabusUploadedOnEnd" => "CourseSection.syllabusUploadedOn <= :syllabusUploadedOnEnd",
            ":cvStatus" => "CourseSection.cvStatus = :cvStatus",
            ":cvUploadedBy" => "CourseSection.cvUploadedBy LIKE :cvUploadedBy",
            ":cvUploadedOnStart" => "CourseSection.cvUploadedOn >= :cvUploadedOnStart",
            ":cvUploadedOnEnd" => "CourseSection.cvUploadedOn <= :cvUploadedOnEnd",
            ":active" => "CourseSection.active = :active"
        ];
    }


    /**
     * @param CourseSectionQueryParams $params
     * @param list<string> $columns
     * @return Query
     */
    public function createQuery(
        CourseSectionQueryParams $params,
        array $columns
    ): Query {
        $orderBy = $params->getOrderBy();
        $params = $this->createParams($params);
        $dql = $this->createDql(
            $columns,
            $params,
            $orderBy
        );

        if (isset($params[":syllabusIsRequired"])) {
            $params[":syllabusIsRequired"] = $params[":syllabusIsRequired"] === "true";
        }

        return $this->em
            ->createQuery($dql)
            ->setParameters($params)
            ->setParameter(":active", true);
    }


    /**
     * @param CourseSectionQueryParams $params
     * @return array<string,string>
     */
    private function createParams(CourseSectionQueryParams $params): array
    {
        $paramValues = $params->getParamValues(
            $this->requiredParams,
            $this->skipParams
        );

        $params = [];
        foreach ($paramValues as $name => $value) {
            $params[":{$name}"] = match ($name) {
                "subjectCode",
                "courseNumber" => $value . '%',
                "courseTitle",
                "instructorId",
                "instructorFirstName",
                "instructorLastName",
                "instructorEmail",
                "syllabusUploadedBy" => '%' . $value . '%',
                "cvUploadedBy" => '%' . $value . '%',
                default => $value
            };
        }

        $params[":active"] = "true";

        return $params;
    }


    /**
     * @param list<string> $columns
     * @param array<string,string> $params
     * @param string|null $orderBy
     * @return string
     */
    private function createDql(
        array $columns,
        array $params,
        string|null $orderBy
    ): string {
        return trim(implode(" ", [
            $this->createSelect($columns),
            $this->createFrom(),
            $this->createWhere($params),
            $this->createOrderBy($orderBy)
        ]));
    }


    /**
     * @param list<string> $columns
     * @return string
     */
    private function createSelect(array $columns): string
    {
        $columns = implode(", ", $columns);
        return "SELECT {$columns}";
    }


    /**
     * @return string
     */
    private function createFrom(): string
    {
        return "FROM {$this->entityName} CourseSection";
    }


    /**
     * @param array<string,string> $params
     * @return string
     */
    private function createWhere(array $params): string
    {
        $where = implode(" AND ", array_filter(
            $this->whereFragments,
            fn(string $key): bool => isset($params[$key]),
            ARRAY_FILTER_USE_KEY
        ));

        return "WHERE {$where}";
    }


    /**
     * @param string|null $orderBy
     * @return string
     */
    private function createOrderBy(string|null $orderBy): string
    {
        return (($orderBy ?? "") !== "")
            ? 'ORDER BY ' . implode(", ", array_map(
                fn($v) => "CourseSection.{$v[0]} " . ($v[1] ?? "ASC"),
                array_map(
                    fn($v) => explode(" ", trim($v)),
                    explode(",", $orderBy ?? "")
                )
            ))
            : "";
    }
}
