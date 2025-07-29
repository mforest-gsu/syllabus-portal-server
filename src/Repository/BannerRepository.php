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
     * @return string
     */
    public function getCurrentTerm(): string
    {
        return "202605";
    }


    /**
     * @return string
     */
    public function getNextTerm(): string
    {
        return "202605";
    }


    /**
     * @return iterable<int,string>
     */
    public function getFutureTerms(): iterable
    {
        yield 0 => "202605";
    }


    /**
     * @param string $termCode
     * @return iterable<int,CourseSection>
     */
    public function getCourseSections(string $termCode): iterable
    {
        return $this->dbGateway->fetch(
            new OracleQuery(__DIR__ . '/SQL/SSBSECT.sql'),
            CourseSection::class,
            [':termCode' => $termCode]
        );
    }
}
