<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TaskUpdateDTO
{
    public function __construct(
        #[Assert\Length(max: 200, maxMessage: 'The title cannot be longer than 200 characters.')]
        public ?string $title = null,

        public ?string $description = null,

        public ?string $priority = null,

        #[Assert\DateTime(format: \DateTimeInterface::ATOM, message: 'Invalid date format.')]
        public ?string $dueDate = null,
    ) {}
}
