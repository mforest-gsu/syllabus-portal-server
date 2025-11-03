<?php

declare(strict_types=1);

namespace Migrations\Gsu\SyllabusPortal;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251103170017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE course_section RENAME COLUMN syllabus_status TO syllabus_status_old");
        $this->addSql("ALTER TABLE course_section ADD COLUMN syllabus_status VARCHAR(12) NOT NULL DEFAULT 'Pending'");
        $this->addSql("ALTER TABLE course_section ADD COLUMN schedule_code VARCHAR(3) NOT NULL DEFAULT 'A'");
        $this->addSql("ALTER TABLE course_section ADD COLUMN syllabus_is_required BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("UPDATE course_section SET syllabus_status = syllabus_status_old");
        $this->addSql("ALTER TABLE course_section DROP COLUMN syllabus_status_old");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE course_section RENAME COLUMN syllabus_status TO syllabus_status_old");
        $this->addSql("UPDATE course_section SET syllabus_status = syllabus_status_old");
        $this->addSql("ALTER TABLE course_section DROP COLUMN syllabus_status_old");
        $this->addSql("ALTER TABLE course_section DROP COLUMN schedule_code");
        $this->addSql("ALTER TABLE course_section DROP COLUMN syllabus_is_required");
    }
}
