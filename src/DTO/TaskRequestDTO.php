<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TaskRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The title is required.')]
        #[Assert\Length(max: 200, maxMessage: 'The title cannot be longer than 200 characters.')]
        public string $title,

        public ?string $description = null,

        public ?string $priority = null,

        #[Assert\DateTime(format: \DateTimeInterface::ATOM, message: 'Invalid date format. Use ATOM/ISO-8601 format.')]
        public ?string $dueDate = null,
    ) {}
}
