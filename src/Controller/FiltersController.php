<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class FiltersController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     * @param class-string<CourseSection> $entityName
     */
    public function __construct(
        private EntityManagerInterface $em,
        private string $entityName = CourseSection::class
    ) {
    }


    #[Route(
        name: 'filters.get_terms',
        methods: 'GET',
        path: '/filters',
        format: 'json'
    )]
    public function getTerms(): JsonResponse
    {
        return new JsonResponse([
            'result' => $this->em
                ->createQuery("
                    SELECT DISTINCT
                        CourseSection.termCode value,
                        CourseSection.termName label
                    FROM
                        {$this->entityName} CourseSection
                    WHERE
                        CourseSection.active = :active
                    ORDER BY
                        CourseSection.termCode desc
                ")
                ->setParameter(':active', true)
                ->getArrayResult()
        ]);
    }


    #[Route(
        name: 'filters.get_colleges',
        methods: 'GET',
        path: '/filters/{termCode}',
        format: 'json'
    )]
    public function getColleges(int $termCode): JsonResponse
    {
        return new JsonResponse([
            'result' => $this->em
                ->createQuery("
                    SELECT DISTINCT
                        CourseSection.collegeCode value,
                        CourseSection.collegeName label
                    FROM
                        {$this->entityName} CourseSection
                    WHERE
                        CourseSection.termCode = :termCode
                    ORDER BY
                        CourseSection.collegeName
                ")
                ->setParameter(':termCode', $termCode)
                ->getArrayResult()
        ]);
    }


    #[Route(
        name: 'filters.get_departments',
        methods: 'GET',
        path: '/filters/{termCode}/{collegeCode}',
        format: 'json'
    )]
    public function getDepartments(
        int $termCode,
        string $collegeCode
    ): JsonResponse {
        return new JsonResponse([
            'result' => $this->em
                ->createQuery("
                    SELECT DISTINCT
                        CourseSection.departmentCode value,
                        CourseSection.departmentName label
                    FROM
                        {$this->entityName} CourseSection
                    WHERE
                        CourseSection.termCode = :termCode AND
                        CourseSection.collegeCode = :collegeCode
                    ORDER BY
                        CourseSection.departmentName
                ")
                ->setParameter(':termCode', $termCode)
                ->setParameter(':collegeCode', $collegeCode)
                ->getArrayResult()
        ]);
    }
}
