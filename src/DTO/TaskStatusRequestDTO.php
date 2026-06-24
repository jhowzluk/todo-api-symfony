<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TaskStatusRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The status field is required.')]
        public string $status,
    ) {}
}
