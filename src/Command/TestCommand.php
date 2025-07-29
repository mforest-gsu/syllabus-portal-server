<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Command;

use Aws\S3\S3Client;
use Gsu\SyllabusPortal\Repository\BannerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:test')]
class TestCommand extends Command
{
    public function __construct(
        private S3Client $s3Client,
        private BannerRepository $bannerRepo,
        string|null $name = null
    ) {
        parent::__construct($name);
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        return self::SUCCESS;
    }


    protected function uploadS3Objects(): void
    {
        $syllabusFiles = glob(__DIR__ . '/../../var/test/202505/*');
        if ($syllabusFiles === false) {
            throw new \RuntimeException('$syllabusFiles === false');
        }

        foreach ($syllabusFiles as $syllabusFilePath) {
            $this->s3Client->putObject([
                'Bucket' => 'shared-data-qa',
                'Key'    => "cetl/test/202505/" . basename($syllabusFilePath),
                'SourceFile' => $syllabusFilePath,
            ]);
        }
    }


    protected function deleteS3Objects(): void
    {
        /** @var array{Key:string}[]|null $contents */
        ['Contents' => $contents] = $this->s3Client->listObjects([
            'Bucket' => 'shared-data-qa',
            'Prefix' => 'cetl/test/2025',
        ]);

        if ($contents !== null) {
            foreach ($contents as $object) {
                $this->s3Client->deleteObject([
                    'Bucket' => 'shared-data-qa',
                    'Key'    => $object['Key']
                ]);
            }
        }
    }


    protected function getMetadata(): void
    {
        /** @var array<string,string> $sections */
        $sections = [];

        $syllabusFiles = glob(__DIR__ . '/../../var/test/202505/*.pdf');
        if ($syllabusFiles === false) {
            throw new \RuntimeException('$syllabusFiles === false');
        }

        foreach ($syllabusFiles as $syllabusFilePath) {
            $crn = basename($syllabusFilePath, '.pdf');
            $sections[$crn] = $crn;
        }

        foreach ($this->bannerRepo->getCourseSections('202505') as $courseSection) {
            $courseSection->init();

            if (isset($sections[$courseSection->crn]) || !$courseSection->hasInstructor()) {
                file_put_contents(
                    sprintf(
                        "%s/%s.json",
                        __DIR__ . '/../../var/test/202505',
                        $courseSection->crn
                    ),
                    json_encode($courseSection, JSON_THROW_ON_ERROR)
                );
            }
        }
    }


    protected function copySyllabusFiles(): void
    {
        $dirName = __DIR__ . '/../../var/test/202505';

        $jsonFiles = glob(__DIR__ . '/../../var/test/202505_old/*.json');
        if ($jsonFiles === false) {
            throw new \RuntimeException('$jsonFiles === false');
        }

        foreach ($jsonFiles as $jsonFilePath) {
            $pdfFilePath = sprintf(
                "%s/%s.pdf",
                dirname($jsonFilePath),
                basename($jsonFilePath, '.json')
            );


            $jsonFileContents = file_get_contents($jsonFilePath);
            if (!is_string($jsonFileContents)) {
                throw new \RuntimeException('!is_string($jsonFileContents)');
            }

            /** @var array{sections:array{termCode:string,crn:string}[]} */
            $metaData = json_decode(
                $jsonFileContents,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            foreach ($metaData['sections'] as ['crn' => $crn]) {
                copy($pdfFilePath, "{$dirName}/{$crn}.pdf");
            }
        }
    }
}
