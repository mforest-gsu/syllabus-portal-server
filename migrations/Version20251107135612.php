<?php

declare(strict_types=1);

namespace Migrations\Gsu\SyllabusPortal;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107135612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX course_section_term_crn');
        $this->addSql(<<<'SQL'
            CREATE INDEX course_section_subj_crse_seq ON course_section (
                term_code,
                subject_code,
                course_number,
                course_sequence
            )
        SQL);
        $this->addSql('CREATE INDEX course_section_term_crn ON course_section (term_code, crn)');
    }

    public function down(Schema $schema): void
    {
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
    }
}
