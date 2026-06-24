<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TaskRequestDTO;
use App\DTO\TaskResponseDTO;
use App\DTO\TaskStatusRequestDTO;
use App\DTO\TaskUpdateDTO;
use App\Entity\Task;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks', name: 'api_tasks_')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tasks = $this->taskService->getAll();

        $data = array_map(fn(Task $task) => TaskResponseDTO::fromEntity($task), $tasks);

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] TaskRequestDTO $dto
    ): JsonResponse {
        $task = $this->taskService->create($dto);

        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Task $task,
        #[MapRequestPayload] TaskRequestDTO $dto
    ): JsonResponse {
        $task = $this->taskService->update($task, $dto);

        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update_partial', methods: ['PATCH'])]
    public function updatePartial(
        Task $task,
        #[MapRequestPayload] TaskUpdateDTO $dto
    ): JsonResponse {
        $task = $this->taskService->updatePartial($task, $dto);

        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_OK);
    }

    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(
        Task $task,
        #[MapRequestPayload] TaskStatusRequestDTO $dto
    ): JsonResponse {
        $task = $this->taskService->updateStatus($task, $dto->status);

        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $this->taskService->delete($task);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
