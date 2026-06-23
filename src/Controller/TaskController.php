<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TaskRequestDTO;
use App\DTO\TaskResponseDTO;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks', name: 'api_tasks_')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();

        $data = array_map(fn(Task $task) => TaskResponseDTO::fromEntity($task), $tasks);

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] TaskRequestDTO $dto
    ): JsonResponse {

        $task = new Task();
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);

        if ($dto->priority) {
            $priority = TaskPriority::tryFrom($dto->priority);
            if ($priority) {
                $task->setPriority($priority);
            }
        }

        if ($dto->dueDate) {
            $task->setDueDate(new \DateTimeImmutable($dto->dueDate));
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json(TaskResponseDTO::fromEntity($task), Response::HTTP_CREATED);
    }
}
