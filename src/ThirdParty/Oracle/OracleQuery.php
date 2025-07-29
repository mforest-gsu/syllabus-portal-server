<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\ThirdParty\Oracle;

class OracleQuery implements \Stringable
{
    public function __construct(string $path)
    {
        $this->setPath($path);
    }


    private string $path = '';
    public function getPath(): string
    {
        return $this->path;
    }
    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }


    public function __toString(): string
    {
        $sql = file_get_contents($this->getPath());
        return is_string($sql)
            ? $sql
            : throw new \RuntimeException();
    }
}
