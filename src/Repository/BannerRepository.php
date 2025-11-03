<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\Term;
use Gsu\SyllabusPortal\ThirdParty\Oracle\OracleGateway;
use Gsu\SyllabusPortal\ThirdParty\Oracle\OracleQuery;

class BannerRepository
{
    /**
     * @param OracleGateway $dbGateway
     */
    public function __construct(private OracleGateway $dbGateway)
    {
    }


    /**
     * @return iterable<int,Term>
     */
    public function getCurrentTerms(): iterable
    {
        return $this->dbGateway->fetch(
            new OracleQuery(__DIR__ . '/SQL/STVTERM.sql'),
            Term::class,
        );
    }


    /**
     * @param string $termCode
     * @return iterable<int,CourseSection>
     */
    public function getCourseSections(string $termCode): iterable
    {
        $crns = [];
        $syllabusVerifications = $this->dbGateway->fetch(
            new OracleQuery(__DIR__ . '/SQL/GSU_SYLLABUS_VERIFICATION.sql'),
            CourseSection::class,
            [':termCode' => $termCode]
        );
        foreach ($syllabusVerifications as $syllabusVerification) {
            $crns[$syllabusVerification->crn] = $syllabusVerification->crn;
        }

        $courseSections = $this->dbGateway->fetch(
            new OracleQuery(__DIR__ . '/SQL/SSBSECT.sql'),
            CourseSection::class,
            [':termCode' => $termCode]
        );

        foreach ($courseSections as $i => $courseSection) {
            $courseSection->syllabusIsRequired = (
                (isset($crns[$courseSection->crn]) || $courseSection->collegeCode === "EH")
                && !in_array($courseSection->subjectCode, ['CREG','GFA','INEX'], true)
                && !in_array($courseSection->scheduleCode, ['E','F','H','M','N','O','P'], true)
                && !in_array($courseSection->collegeCode, ['00','UN'], true)
            );

            yield $i => $courseSection;
        }
    }
}
