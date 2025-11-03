<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Command;

use Gsu\SyllabusPortal\Repository\BannerRepository;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:update-course-sections')]
class UpdateCourseSectionsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    public function __construct(
        private CourseSectionRepository $courseSectionRepo,
        private BannerRepository $bannerRepo,
        string|null $name = null
    ) {
        parent::__construct($name);
    }


    protected function configure(): void
    {
        $this->addArgument('term_code', InputArgument::OPTIONAL);
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->courseSectionRepo->setAllInactive();

        // Run for each available term
        foreach ($this->getAvailableTerms($input) as $termCode) {
            // Fetch new course sections from Banner
            $newSections = $this->bannerRepo->getCourseSections($termCode);
            $this->logger?->info(sprintf(
                "Fetched course sections for term '%s'",
                $termCode
            ));

            // Update local course sections
            $result = $this->courseSectionRepo->update(
                $termCode,
                $newSections
            );
            $this->logger?->info(sprintf(
                "Updated course sections for term '%s'; +%s, ~%s, -%s; %s",
                $termCode,
                $result['created'],
                $result['updated'],
                $result['deleted'],
                $result['total']
            ));
        }

        return self::SUCCESS;
    }


    /**
     * @param InputInterface $input
     * @return iterable<int,string>
     */
    private function getAvailableTerms(InputInterface $input): iterable
    {
        $termCode = $input->getArgument('term_code');
        if (is_numeric($termCode)) {
            yield 0 => strval($termCode);
        } else {
            foreach ($this->bannerRepo->getCurrentTerms() as $i => $term) {
                yield $i => $term->termCode;
            }
        }
    }
}
