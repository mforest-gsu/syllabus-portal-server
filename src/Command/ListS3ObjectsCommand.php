<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Command;

use Aws\S3\S3Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("app:list-s3-objects")]
class ListS3ObjectsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private S3Client $s3Client,
        private string $bucket,
        private string $prefix,
        string|null $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument("prefix", InputArgument::OPTIONAL);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $prefix = $input->getArgument("prefix");
        $prefix = is_scalar($prefix) ? strval($prefix) : "";

        foreach ($this->list($prefix) as $object) {
            /** @var non-empty-string $objstr */
            $objstr = json_encode($object, JSON_THROW_ON_ERROR);
            $output->writeln($objstr);
        }

        return self::SUCCESS;
    }

    /**
     * @param string $prefix
     * @return iterable<string,array{Key:string,Size:string,LastModified:\Stringable}>
     */
    public function list(string $prefix): iterable
    {
        $pageToken = null;

        do {
            $result = $this->s3Client->listObjectsV2([
                "Bucket" => $this->bucket,
                "Prefix" => sprintf("%s/%s", $this->prefix, $prefix),
                "MaxKeys" => 1000,
                "ContinuationToken" => $pageToken,
            ]);

            /**
             * @var string|null $pageToken
             */
            $pageToken = $result["NextContinuationToken"] ?? null;

            /**
             * @var array{Key:string,Size:string,LastModified:\Stringable}[] $objects
             */
            $objects = $result["Contents"] ?? [];

            foreach ($objects as $object) {
                yield $object["Key"] => $object;
            }
        } while ($pageToken !== null);
    }
}
