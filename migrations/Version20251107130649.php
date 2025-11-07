<?php

declare(strict_types=1);

namespace Migrations\Gsu\SyllabusPortal;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107130649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX course_section_instructor_cv ON course_section (instructor_id, cv_status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX course_section_instructor_cv');
    }
}
