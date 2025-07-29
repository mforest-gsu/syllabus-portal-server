<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\ThirdParty\Oracle;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class OracleGateway
{
    /**
     * @var resource|false $db
     */
    private mixed $db = false;


    /**
     * @param DenormalizerInterface $serializer
     * @param string $oracleUrl
     * @param string $oracleUser
     * @param string $oraclePass
     * @param string $oracleCharset
     * @param int $oracleSessionMode
     */
    public function __construct(
        private DenormalizerInterface $serializer,
        private string $oracleUrl,
        private string $oracleUser = "/",
        private string $oraclePass = "",
        private string $oracleCharset = "UTF8",
        // Default value is OCI_CRED_EXT, but oci not available in web env
        private int $oracleSessionMode = -2147483648
    ) {
    }


    public function __destruct()
    {
        if (is_resource($this->db)) {
            oci_close($this->db);
        }
    }


    /**
     * @template T of object
     * @param string|\Stringable $query
     * @param class-string<T> $rowClass
     * @param array<string,scalar|array{var:mixed,maxlength?:int,type?:int}>|null $params
     * @return T|null
     */
    public function fetchFirst(
        string|\Stringable $query,
        string $rowClass,
        array|null $params = null
    ): mixed {
        $rows = $this->fetch(
            $query,
            $rowClass,
            $params,
            1
        );

        foreach ($rows as $row) {
            return $row;
        }

        return null;
    }


    /**
     * @template T of object
     * @param string|\Stringable $query
     * @param class-string<T> $rowClass
     * @param array<string,scalar|array{var:mixed,maxlength?:int,type?:int}>|null $params
     * @param int $prefetch
     * @return iterable<int,T>
     */
    public function fetch(
        string|\Stringable $query,
        string $rowClass,
        array|null $params = null,
        int $prefetch = 5000,
    ): iterable {
        try {
            $stmt = oci_parse($this->getDB(), (string) $query);
            if (!is_resource($stmt)) {
                throw $this->error($this->getDB());
            }

            if (is_array($params)) {
                foreach (array_keys($params) as $key) {
                    if (is_array($params[$key])) {
                        oci_bind_by_name(
                            $stmt,
                            $key,
                            $params[$key]['var'],
                            $params[$key]['maxlength'] ?? -1,
                            $params[$key]['type'] ?? SQLT_CHR,
                        );
                    } else {
                        oci_bind_by_name(
                            $stmt,
                            $key,
                            $params[$key]
                        );
                    }
                }
            }

            oci_set_prefetch($stmt, $prefetch);

            if (oci_execute($stmt, OCI_DEFAULT) === false) {
                throw $this->error($stmt);
            }

            $rowNum = 0;
            for ($row = oci_fetch_assoc($stmt); is_array($row); $row = oci_fetch_assoc($stmt)) {
                /** @var T $record */
                $record = $this->serializer->denormalize($row, $rowClass);
                yield ++$rowNum => $record;
            }
        } finally {
            if (is_resource($stmt ?? null)) {
                oci_free_statement($stmt);
            }
        }
    }





    /**
     * @return resource
     */
    private function getDB(): mixed
    {
        if (is_resource($this->db)) {
            return $this->db;
        }

        $this->db = oci_connect(
            $this->oracleUser,
            $this->oraclePass,
            $this->oracleUrl,
            $this->oracleCharset,
            $this->oracleSessionMode
        );

        if (!is_resource($this->db)) {
            throw $this->error("Error connecting to Oracle database");
        }

        return $this->db;
    }


    /**
     * @param resource|string|null $resource
     * @return \Throwable
     */
    private function error(mixed $resource = null): \Throwable
    {
        /** @var array{code:int,message:string,offset:int,sqltext:string}|false $error */
        $error = is_string($resource)
            ? [
                'code' => 0,
                'message' => $resource,
                'offset' => 0,
                'sqltext' => ''
            ]
            : oci_error($resource);

        return is_array($error)
            ? new \Exception(
                $error['message'],
                $error['code']
            )
            : new \Exception();
    }
}
