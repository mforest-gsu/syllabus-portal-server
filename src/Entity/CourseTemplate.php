<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

class CourseTemplate
{
    public string $departmentCode = '';
    public string $courseCode = '';

    public function __construct(public string $courseTemplate = '')
    {

        [, $this->departmentCode, $this->courseCode] = [...explode('.', $courseTemplate), "", "", ""];
    }
}
