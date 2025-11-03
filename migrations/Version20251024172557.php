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
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_status VARCHAR(10) NOT NULL DEFAULT 'Pending'");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_key VARCHAR(128) DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_extension VARCHAR(16) DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_uploaded_on DATETIME DEFAULT NULL");
        $this->addSql("ALTER TABLE course_section ADD COLUMN cv_uploaded_by VARCHAR(128) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_status");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_key");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_extension");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_uploaded_on");
        $this->addSql("ALTER TABLE course_section DROP COLUMN cv_uploaded_by");
    }
}
