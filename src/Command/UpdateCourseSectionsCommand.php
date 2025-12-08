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

        foreach ($this->getAvailableTerms($input) as $termCode) {
            $this->logger?->info(sprintf("Term: %s", $termCode));

            $result = $this->courseSectionRepo->update(
                $termCode,
                $this->bannerRepo->getCourseSections($termCode)
            );

            $result['deleted'] = $this->courseSectionRepo->removeInactive($termCode);

            $this->logger?->info(sprintf(
                "Term '%s'; Created: %s; Updated: %s, Deleted: %s; Total: %s",
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
