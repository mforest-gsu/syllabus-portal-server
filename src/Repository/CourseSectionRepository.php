<?php

namespace Gsu\SyllabusPortal\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/** @extends ServiceEntityRepository<CourseSection> */
final class CourseSectionRepository extends ServiceEntityRepository
{
    private CourseSectionQueryBuilder $queryBuilder;


    public function __construct(
        ManagerRegistry $registry,
        private SyllabusRepository $syllabusRepo,
        private Security $security
    ) {
        parent::__construct($registry, CourseSection::class);
        $this->queryBuilder = new CourseSectionQueryBuilder($this->getEntityManager());
    }


    /**
     * @param mixed $id
     * @param LockMode|int|null $lockMode
     * @phpstan-param LockMode::*|null $lockMode
     * @return CourseSection|null
     */
    public function find(
        mixed $id,
        LockMode|int|null $lockMode = null,
        int|null $lockVersion = null
    ): object|null {
        $courseSection = parent::find($id, $lockMode, $lockVersion);
        return ($courseSection instanceof CourseSection)
            ? $courseSection
            : null;
    }


    /**
     * @param mixed $id
     * @return CourseSection|null
     */
    public function findWithUrl(mixed $id): object|null
    {
        $courseSection = $this->find($id);
        if ($courseSection instanceof CourseSection) {
            $courseSection->syllabusUrl = $this->syllabusRepo->getDownloadUrl(
                $courseSection,
                $this->getExpiresIn()
            );
        }

        return $courseSection;
    }


    /**
     * @param CourseSectionQueryParams $params
     * @return array{0:CourseSection[],1:int}
     */
    public function fetch(CourseSectionQueryParams $params): array
    {
        /** @var CourseSection[] $data */
        $data = $this->queryBuilder
            ->createQuery($params, ["CourseSection"])
            ->setFirstResult($params->getOffset())
            ->setMaxResults(min(max($params->getLimit(), 1), 100))
            ->getResult();

        $countParams = clone $params;
        $countParams->setOrderBy(null);
        $count = $this->queryBuilder
            ->createQuery($countParams, ["count(1)"])
            ->getSingleScalarResult();
        $count = is_numeric($count) ? intval($count) : 0;

        foreach ($data as $courseSection) {
            $courseSection->syllabusUrl = $courseSection->hasSyllabus()
                ? $this->syllabusRepo->getDownloadUrl(
                    $courseSection,
                    $this->getExpiresIn()
                )
                : null;
        }

        return [$data, $count];
    }


    /**
     * @param string $termCode
     * @param iterable<int,CourseSection> $newSections
     * @return array{created:int,updated:int,deleted:int,total:int}
     */
    public function update(
        string $termCode,
        iterable $newSections
    ): array {
        $em = $this->getEntityManager();
        $created = $updated = $total = 0;

        $this->setInactive($termCode);

        foreach ($newSections as $newSection) {
            if ($newSection->termCode !== $termCode) {
                continue;
            }

            $newSection->init();

            /** @var CourseSection|null $currentSection */
            $currentSection = $this->find($newSection->id);
            if ($currentSection === null) {
                if (!$newSection->hasInstructor()) {
                    $this->syllabusRepo->addSyllabusMetadata($newSection);
                }
                $em->persist($newSection);
                $created++;
            } else {
                $usedToHaveInstructor = $currentSection->hasInstructor();
                $nowHasInstuctor = $newSection->hasInstructor();

                $currentSection->active = true;
                if ($currentSection->setValues($newSection)) {
                    if (!$usedToHaveInstructor && $nowHasInstuctor) {
                        $this->syllabusRepo->removeSyllabusMetadata($currentSection);
                    } elseif (!$nowHasInstuctor) {
                        if ($usedToHaveInstructor && $currentSection->hasSyllabus()) {
                            $this->syllabusRepo->removeSyllabus($currentSection);
                        }
                        $this->syllabusRepo->addSyllabusMetadata($currentSection);
                    }
                    $updated++;
                }
            }

            if (++$total % 250 === 0) {
                $em->flush();
            }
        }

        $em->flush();

        $deleted = $this->removeInactive($termCode);

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'total' => $total
        ];
    }


    public function setInactive(string $termCode): int
    {
        $courseSection = CourseSection::class;
        $updated = $this
            ->getEntityManager()
            ->createQuery("
                UPDATE
                    {$courseSection} CourseSection
                SET
                    CourseSection.active = :active
                WHERE
                    CourseSection.termCode = :termCode
            ")
            ->execute([
                ':termCode' => $termCode,
                ':active' => false
            ]);

        return is_numeric($updated) ? intval($updated) : 0;
    }


    public function removeInactive(string $termCode): int
    {
        $courseSectionClass = CourseSection::class;
        $dql = "
            SELECT
                CourseSection
            FROM
                {$courseSectionClass} CourseSection
            WHERE
                CourseSection.termCode = :termCode AND
                CourseSection.active = :active
        ";
        $params = [
            ':termCode' => $termCode,
            ':active' => false
        ];

        /** @var CourseSection[] $data */
        $data = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameters($params)
            ->getResult();

        $deletedTotal = $deleted = 0;
        foreach ($data as $courseSection) {
            if ($courseSection->hasSyllabus()) {
                //$this->syllabusRepo->removeSyllabus($courseSection);
                $courseSection->active = true;
            } else {
                if (!$courseSection->hasInstructor()) {
                    $this->syllabusRepo->removeSyllabusMetadata($courseSection);
                }
                $this->getEntityManager()->remove($courseSection);
                $deleted++;
            }

            if (++$deletedTotal % 250 === 0) {
                $this->getEntityManager()->flush();
            }
        }

        $this->getEntityManager()->flush();

        return $deleted;
    }


    private function getUser(): User|null
    {
        $user = $this->security->getUser();
        return ($user instanceof User)
            ? $user
            : null;
    }


    private function getExpiresIn(): int
    {
        $user = $this->getUser();
        return ($user !== null) ? $user->getExp() - time() : 3600;
    }
}
