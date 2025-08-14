<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gsu\SyllabusPortal\Entity\CourseSection;
use Gsu\SyllabusPortal\Entity\User;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Gsu\SyllabusPortal\Repository\SyllabusRepository;
use Gsu\SyllabusPortal\ThirdParty\Oracle\OracleGateway;
use Gsu\SyllabusPortal\ThirdParty\Oracle\OracleQuery;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:batch-upload')]
class BatchUploadCourseSectionsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseSectionRepository $courseSectionRepo,
        private SyllabusRepository $syllabusRepo,
        private OracleGateway $dbGateway,
        string|null $name = null
    ) {
        parent::__construct($name);
    }


    protected function configure(): void
    {
        $this->addArgument('term_code', InputArgument::REQUIRED);
        $this->addArgument('batch_dir', InputArgument::REQUIRED);
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $termCode = $input->getArgument('term_code');
        $termCode = is_numeric($termCode)
            ? strval(intval($termCode))
            : throw new \RuntimeException("Invalid term_code");

        $batchDir = $input->getArgument('batch_dir');
        $batchDir = is_string($batchDir) && is_dir($batchDir)
            ? $batchDir
            : throw new \RuntimeException("Invalid batch_dir");

        $getCrn = fn (string $syllabusFilePath): string => array_reverse(
            explode('_', basename($syllabusFilePath, '.pdf'))
        )[0];

        $batchSections = $this->getBatchSections($termCode, $batchDir, $getCrn);

        $this->getMetadata($batchSections);

        return self::SUCCESS;
    }


    /**
     * @param string $termCode
     * @param string $batchDir
     * @param (callable(string $filePath):string) $getCrn
     * @return iterable<int,CourseSection>
     */
    private function getBatchSections(
        string $termCode,
        string $batchDir,
        callable $getCrn
    ): iterable {
        /** @var array<string,string> $sections */
        $sections = [];

        $syllabusFiles = glob("{$batchDir}/*.pdf");
        if ($syllabusFiles === false) {
            throw new \RuntimeException('$syllabusFiles === false');
        }

        foreach ($syllabusFiles as $syllabusFilePath) {
            $crn = $getCrn($syllabusFilePath);
            $sections[$crn] = $syllabusFilePath;
        }

        $courseSections = $this->dbGateway->fetch(
            new OracleQuery(__DIR__ . '/../Repository/SQL/SSBSECT.sql'),
            CourseSection::class,
            [':termCode' => $termCode]
        );

        foreach ($courseSections as $i => $courseSection) {
            if (isset($sections[$courseSection->crn])) {
                $courseSection->syllabusUrl = $sections[$courseSection->crn];
                yield $i => $courseSection;
            }
        }
    }


    /**
     * @param iterable<int,CourseSection> $batchSections
     * @return void
     */
    protected function getMetadata(iterable $batchSections): void
    {
        $created = $updated = $total = 0;

        foreach ($batchSections as $newSection) {
            $syllabusFilePath = $newSection->syllabusUrl;
            if ($syllabusFilePath === null) {
                continue;
            }

            $newSection->init();
            $newSection->syllabusUrl = null;

            /** @var CourseSection|null $currentSection */
            $currentSection = $this->courseSectionRepo->find($newSection->id);
            if ($currentSection === null) {
                $this->syllabusRepo->addSyllabus(
                    $newSection,
                    new User(preferredUsername: 'mforest@gsu.edu'),
                    $syllabusFilePath
                );
                $this->entityManager->persist($newSection);
                $created++;
            } else {
                $currentSection->setValues($newSection);
                $this->syllabusRepo->addSyllabus(
                    $currentSection,
                    new User(preferredUsername: 'mforest@gsu.edu'),
                    $syllabusFilePath
                );
                $updated++;
            }

            if (++$total % 250 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->logger?->info(sprintf(
            "Created: %s; Updated: %s; Total: %s",
            $created,
            $updated,
            $total
        ));
    }
}
