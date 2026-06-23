<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Task;

final readonly class TaskResponseDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $status,
        public string $priority,
        public string $createdAt,
        public string $updatedAt,
        public ?string $dueDate,
    ) {}

    public static function fromEntity(Task $task): self
    {
        return new self(
            id: $task->getId(),
            title: $task->getTitle(),
            description: $task->getDescription(),
            status: $task->getStatus()?->label() ?? '',
            priority: $task->getPriority()?->label() ?? '',
            createdAt: $task->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '',
            updatedAt: $task->getUpdatedAt()?->format(\DateTimeInterface::ATOM) ?? '',
            dueDate: $task->getDueDate()?->format(\DateTimeInterface::ATOM),
        );
    }
}
