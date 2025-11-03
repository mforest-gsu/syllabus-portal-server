<?php

declare(strict_types=1);

namespace Migrations\Gsu\SyllabusPortal;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250728163952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE course_section (
                id VARCHAR(12) NOT NULL,
                term_code VARCHAR(6) NOT NULL,
                term_name VARCHAR(30) NOT NULL,
                crn VARCHAR(5) NOT NULL,
                college_code VARCHAR(2) NOT NULL,
                college_name VARCHAR(100) NOT NULL,
                department_code VARCHAR(4) NOT NULL,
                department_name VARCHAR(30) NOT NULL,
                campus_code VARCHAR(3) NOT NULL,
                campus_name VARCHAR(30) NOT NULL,
                course_effective_term VARCHAR(6) NOT NULL,
                subject_code VARCHAR(4) NOT NULL,
                course_number VARCHAR(5) NOT NULL,
                course_sequence VARCHAR(3) NOT NULL,
                course_title VARCHAR(30) NOT NULL,
                instructor_pidm VARCHAR(8) NOT NULL,
                instructor_id VARCHAR(9) NOT NULL,
                instructor_first_name VARCHAR(60) DEFAULT NULL,
                instructor_last_name VARCHAR(60) DEFAULT NULL,
                instructor_email VARCHAR(128) DEFAULT NULL,
                syllabus_status VARCHAR(10) NOT NULL DEFAULT 'Pending',
                syllabus_key VARCHAR(128) DEFAULT NULL,
                syllabus_extension VARCHAR(16) DEFAULT NULL,
                syllabus_uploaded_on DATETIME DEFAULT NULL,
                syllabus_uploaded_by VARCHAR(128) DEFAULT NULL,
                active BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX course_section_term_crn ON course_section (
                term_code,
                subject_code,
                course_number,
                course_sequence
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE course_section");
    }
}
