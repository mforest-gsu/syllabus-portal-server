<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\ThirdParty\AWS;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class S3ClientFactory
{
    public static function create(Credentials $credentials): S3Client
    {
        return new S3Client([
            'region' => 'us-east-1',
            'credentials' => $credentials
        ]);
    }
}
