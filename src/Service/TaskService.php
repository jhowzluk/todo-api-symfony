<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\TaskRequestDTO;
use App\DTO\TaskUpdateDTO;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * @return Task[]
     */
    public function getAll(): array
    {
        return $this->taskRepository->findAll();
    }

    public function create(TaskRequestDTO $dto): Task
    {
        $task = new Task();
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);

        if ($dto->priority !== null) {
            $task->setPriority($this->resolvePriority($dto->priority));
        }

        if ($dto->dueDate !== null) {
            $task->setDueDate(new \DateTimeImmutable($dto->dueDate));
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function update(Task $task, TaskRequestDTO $dto): Task
    {
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);

        if ($dto->priority !== null) {
            $task->setPriority($this->resolvePriority($dto->priority));
        } else {
            $task->setPriority(TaskPriority::MEDIUM);
        }

        $task->setDueDate($dto->dueDate ? new \DateTimeImmutable($dto->dueDate) : null);

        $this->entityManager->flush();

        return $task;
    }

    public function updatePartial(Task $task, TaskUpdateDTO $dto): Task
    {
        if ($dto->title !== null) {
            $task->setTitle($dto->title);
        }

        if ($dto->description !== null) {
            $task->setDescription($dto->description);
        }

        if ($dto->priority !== null) {
            $task->setPriority($this->resolvePriority($dto->priority));
        }

        if ($dto->dueDate !== null) {
            $task->setDueDate(new \DateTimeImmutable($dto->dueDate));
        }

        $this->entityManager->flush();

        return $task;
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $newStatus = TaskStatus::tryFrom($status);

        if (!$newStatus) {
            throw new \InvalidArgumentException('Invalid status. Allowed values: pending, in_progress, completed, cancelled.');
        }

        $task->setStatus($newStatus);
        $this->entityManager->flush();

        return $task;
    }

    public function delete(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    private function resolvePriority(string $priority): TaskPriority
    {
        $resolved = TaskPriority::tryFrom($priority);

        if (!$resolved) {
            throw new \InvalidArgumentException('Invalid priority. Allowed values: low, medium, high, urgent.');
        }

        return $resolved;
    }
}
