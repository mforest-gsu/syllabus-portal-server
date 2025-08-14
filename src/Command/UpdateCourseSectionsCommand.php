<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Command;

use Gsu\SyllabusPortal\Repository\BannerRepository;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:update-course-sections')]
class UpdateCourseSectionsCommand extends Command
{
    public function __construct(
        private CourseSectionRepository $courseSectionRepo,
        private BannerRepository $bannerRepo,
        string|null $name = null
    ) {
        parent::__construct($name);
    }


    protected function configure(): void
    {
        $this->addArgument('term_code', InputArgument::REQUIRED);
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        // Determine what term to process
        $termCode = $this->getTermCode($input);
        $output->writeln(sprintf(
            "Updating course sections for term '%s'",
            $termCode
        ));

        // Fetch new course sections from Banner
        $newSections = $this->bannerRepo->getCourseSections($termCode);
        $output->writeln(sprintf(
            "Fetched course sections for term '%s'",
            $termCode
        ));

        // Update local course sections
        $result = $this->courseSectionRepo->update(
            $termCode,
            $newSections
        );
        $output->writeln(sprintf(
            "Term: %s; Created: %s; Updated: %s; Deleted: %s; Total: %s",
            $termCode,
            $result['created'],
            $result['updated'],
            $result['deleted'],
            $result['total']
        ));

        return self::SUCCESS;
    }


    /**
     * @param InputInterface $input
     * @return string
     */
    private function getTermCode(InputInterface $input): string
    {
        $termCode = $input->getArgument('term_code');
        return is_numeric($termCode)
                ? strval(intval($termCode))
                : throw new \RuntimeException("Invalid term_code");
    }
}
