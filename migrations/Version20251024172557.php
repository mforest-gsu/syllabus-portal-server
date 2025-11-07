<?php

declare(strict_types=1);

namespace Migrations\Gsu\SyllabusPortal;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024172557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE course_section ADD COLUMN schedule_code VARCHAR(3) DEFAULT 'A'");
        $this->addSql("ALTER TABLE course_section ADD COLUMN syllabus_is_required BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_status VARCHAR(10) NOT NULL DEFAULT 'Pending'");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_key VARCHAR(128) DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_extension VARCHAR(16) DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_uploaded_on DATETIME DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_uploaded_by VARCHAR(128) DEFAULT NULL");
        $this->addSql('DROP INDEX course_section_term_crn');
        $this->addSql('CREATE INDEX course_section_term_crn ON course_section (term_code, crn)');
        $this->addSql('CREATE INDEX course_section_instructor_cv ON course_section (instructor_id, cv_status)');
        $this->addSql(<<<'SQL'
            CREATE INDEX course_section_subj_crse_seq ON course_section (
                term_code,
                subject_code,
                course_number,
                course_sequence
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX course_section_instructor_cv');
        $this->addSql('DROP INDEX course_section_subj_crse_seq');
        $this->addSql('DROP INDEX course_section_term_crn');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX course_section_term_crn ON course_section (
                term_code,
                subject_code,
                course_number,
                course_sequence
            )
        SQL);
        $this->addSql("ALTER TABLE course_section DROP COLUMN schedule_code");
        $this->addSql("ALTER TABLE course_section DROP COLUMN syllabus_is_required");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_status");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_key");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_extension");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_uploaded_on");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_uploaded_by");
    }
}
