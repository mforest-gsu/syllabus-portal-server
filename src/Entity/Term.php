<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

class Term
{
    public function __construct(
        public string $termCode = '',
        public string $termName = ''
    ) {
    }
}
