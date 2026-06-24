<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\DTO\TaskRequestDTO;
use App\DTO\TaskUpdateDTO;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class TaskServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->service = new TaskService($this->taskRepository, $this->entityManager);
    }

    public function testCreateTaskWithMinimalData(): void
    {
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $dto = new TaskRequestDTO(title: 'New task');
        $task = $this->service->create($dto);

        $this->assertSame('New task', $task->getTitle());
        $this->assertNull($task->getDescription());
        $this->assertSame(TaskPriority::MEDIUM, $task->getPriority());
    }

    public function testCreateTaskWithAllFields(): void
    {
        $dto = new TaskRequestDTO(
            title: 'Full task',
            description: 'A description',
            priority: 'high',
            dueDate: '2026-12-31T10:00:00+00:00'
        );

        $task = $this->service->create($dto);

        $this->assertSame('Full task', $task->getTitle());
        $this->assertSame('A description', $task->getDescription());
        $this->assertSame(TaskPriority::HIGH, $task->getPriority());
        $this->assertNotNull($task->getDueDate());
    }

    public function testCreateTaskWithInvalidPriorityThrows(): void
    {
        $dto = new TaskRequestDTO(title: 'Task', priority: 'banana');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid priority');

        $this->service->create($dto);
    }

    public function testUpdateTask(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $task = new Task();
        $task->setTitle('Old title');

        $dto = new TaskRequestDTO(title: 'New title', description: 'New desc', priority: 'low');
        $result = $this->service->update($task, $dto);

        $this->assertSame('New title', $result->getTitle());
        $this->assertSame('New desc', $result->getDescription());
        $this->assertSame(TaskPriority::LOW, $result->getPriority());
    }

    public function testUpdateTaskWithoutPriorityResetsToMedium(): void
    {
        $task = new Task();
        $task->setTitle('Task');
        $task->setPriority(TaskPriority::URGENT);

        $dto = new TaskRequestDTO(title: 'Task');
        $result = $this->service->update($task, $dto);

        $this->assertSame(TaskPriority::MEDIUM, $result->getPriority());
    }

    public function testUpdatePartialOnlyChangesProvidedFields(): void
    {
        $task = new Task();
        $task->setTitle('Original');
        $task->setDescription('Original desc');
        $task->setPriority(TaskPriority::LOW);

        $dto = new TaskUpdateDTO(title: 'Updated');
        $result = $this->service->updatePartial($task, $dto);

        $this->assertSame('Updated', $result->getTitle());
        $this->assertSame('Original desc', $result->getDescription());
        $this->assertSame(TaskPriority::LOW, $result->getPriority());
    }

    public function testUpdatePartialWithInvalidPriorityThrows(): void
    {
        $task = new Task();
        $task->setTitle('Task');

        $dto = new TaskUpdateDTO(priority: 'invalid');

        $this->expectException(\InvalidArgumentException::class);

        $this->service->updatePartial($task, $dto);
    }

    public function testUpdateStatusWithValidStatus(): void
    {
        $task = new Task();
        $task->setTitle('Task');

        $result = $this->service->updateStatus($task, 'in_progress');

        $this->assertSame(TaskStatus::IN_PROGRESS, $result->getStatus());
    }

    public function testUpdateStatusWithInvalidStatusThrows(): void
    {
        $task = new Task();
        $task->setTitle('Task');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status');

        $this->service->updateStatus($task, 'banana');
    }

    public function testUpdateStatusWithInvalidTransitionThrows(): void
    {
        $task = new Task();
        $task->setTitle('Task');

        $this->expectException(\DomainException::class);

        $this->service->updateStatus($task, 'completed');
    }

    public function testDeleteTask(): void
    {
        $this->entityManager->expects($this->once())->method('remove');
        $this->entityManager->expects($this->once())->method('flush');

        $task = new Task();
        $task->setTitle('To delete');

        $this->service->delete($task);
    }

    public function testGetAll(): void
    {
        $tasks = [new Task(), new Task()];
        $this->taskRepository->method('findAll')->willReturn($tasks);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }
}
