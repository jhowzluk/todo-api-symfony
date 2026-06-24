<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    private function createTaskWithStatus(TaskStatus $status): Task
    {
        $task = new Task();
        $task->setTitle('Test task');

        $reflection = new \ReflectionClass($task);
        $property = $reflection->getProperty('status');
        $property->setValue($task, $status);

        return $task;
    }

    public function testTaskIsCreatedWithPendingStatusAndMediumPriority(): void
    {
        $task = new Task();
        $task->setTitle('Learn PHPUnit');

        $this->assertSame(TaskStatus::PENDING, $task->getStatus());
        $this->assertSame(TaskPriority::MEDIUM, $task->getPriority());
    }

    public function testCanTransitionFromPendingToInProgress(): void
    {
        $task = new Task();
        $task->setTitle('Test');

        $task->setStatus(TaskStatus::IN_PROGRESS);

        $this->assertSame(TaskStatus::IN_PROGRESS, $task->getStatus());
    }

    public function testCanTransitionFromPendingToCancelled(): void
    {
        $task = new Task();
        $task->setTitle('Test');

        $task->setStatus(TaskStatus::CANCELLED);

        $this->assertSame(TaskStatus::CANCELLED, $task->getStatus());
    }

    public function testCanTransitionFromInProgressToCompleted(): void
    {
        $task = $this->createTaskWithStatus(TaskStatus::IN_PROGRESS);

        $task->setStatus(TaskStatus::COMPLETED);

        $this->assertSame(TaskStatus::COMPLETED, $task->getStatus());
    }

    public function testCanTransitionFromInProgressToCancelled(): void
    {
        $task = $this->createTaskWithStatus(TaskStatus::IN_PROGRESS);

        $task->setStatus(TaskStatus::CANCELLED);

        $this->assertSame(TaskStatus::CANCELLED, $task->getStatus());
    }

    public function testCannotTransitionFromCompletedToAnyStatus(): void
    {
        $task = $this->createTaskWithStatus(TaskStatus::COMPLETED);

        $this->expectException(\DomainException::class);

        $task->setStatus(TaskStatus::PENDING);
    }

    public function testCannotTransitionFromCancelledToAnyStatus(): void
    {
        $task = $this->createTaskWithStatus(TaskStatus::CANCELLED);

        $this->expectException(\DomainException::class);

        $task->setStatus(TaskStatus::IN_PROGRESS);
    }

    public function testCannotTransitionFromPendingToCompleted(): void
    {
        $task = new Task();
        $task->setTitle('Test');

        $this->expectException(\DomainException::class);

        $task->setStatus(TaskStatus::COMPLETED);
    }

    public function testCannotTransitionFromInProgressToPending(): void
    {
        $task = $this->createTaskWithStatus(TaskStatus::IN_PROGRESS);

        $this->expectException(\DomainException::class);

        $task->setStatus(TaskStatus::PENDING);
    }

    public function testSetTitleAndDescription(): void
    {
        $task = new Task();
        $task->setTitle('My title');
        $task->setDescription('My description');

        $this->assertSame('My title', $task->getTitle());
        $this->assertSame('My description', $task->getDescription());
    }

    public function testSetPriority(): void
    {
        $task = new Task();
        $task->setTitle('Test');
        $task->setPriority(TaskPriority::URGENT);

        $this->assertSame(TaskPriority::URGENT, $task->getPriority());
    }

    public function testSetDueDate(): void
    {
        $task = new Task();
        $task->setTitle('Test');
        $dueDate = new \DateTimeImmutable('2026-12-31T23:59:59+00:00');
        $task->setDueDate($dueDate);

        $this->assertSame($dueDate, $task->getDueDate());
    }

    public function testPrePersistSetsTimestamps(): void
    {
        $task = new Task();
        $task->setTitle('Test');
        $task->setCreatedAtValue();

        $this->assertNotNull($task->getCreatedAt());
        $this->assertNotNull($task->getUpdatedAt());
    }
}
