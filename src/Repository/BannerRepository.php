<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Gsu\SyllabusPortal\Entity\CourseSection;
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
     * @param string $termCode
     * @return iterable<int,CourseSection>
     */
    public function getCourseSections(string $termCode): iterable
    {
        if ($termCode !== "202508") {
            return $this->dbGateway->fetch(
                new OracleQuery(__DIR__ . '/SQL/SSBSECT.sql'),
                CourseSection::class,
                [':termCode' => $termCode]
            );
        }

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
            if (isset($crns[$courseSection->crn]) || $courseSection->collegeCode === "EH") {
                yield $i => $courseSection;
            }
        }
    }
}
