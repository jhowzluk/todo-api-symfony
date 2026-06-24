# Todo API

> REST API for task management built with Symfony 7.4, featuring CRUD operations, status transitions with business rules, data validation, and standardized JSON responses.

![Status](https://img.shields.io/badge/Status-completed-green)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![Symfony](https://img.shields.io/badge/Symfony-7.4-purple)
![Database](https://img.shields.io/badge/Database-PostgreSQL-blue)

## Table of Contents

- [Technologies](#technologies)
- [Folder Structure](#folder-structure)
- [Main Entity](#main-entity)
- [API Endpoints](#api-endpoints)
- [Status Transitions](#status-transitions)
- [How to Run](#how-to-run)
- [Running Tests](#running-tests)

## Technologies

| Technology | Role in the Project |
|---|---|
| PHP 8.3 | Main backend language |
| Symfony 7.4 | Web framework |
| Doctrine ORM | Database mapping and migrations |
| PostgreSQL 16 | Relational database |
| PHPUnit 12 | Testing framework |
| Composer | Dependency management and PSR-4 autoloading |

## Folder Structure

```
todo-api-symfony/
├── config/
│   ├── packages/           # Bundle configurations
│   ├── routes.yaml         # Route configuration
│   └── services.yaml       # Service container configuration
├── migrations/             # Doctrine database migrations
├── public/
│   └── index.php           # Application entry point
├── src/
│   ├── Controller/
│   │   └── TaskController.php
│   ├── DTO/
│   │   ├── TaskRequestDTO.php
│   │   ├── TaskResponseDTO.php
│   │   ├── TaskStatusRequestDTO.php
│   │   └── TaskUpdateDTO.php
│   ├── Entity/
│   │   └── Task.php
│   ├── Enum/
│   │   ├── TaskPriority.php
│   │   └── TaskStatus.php
│   ├── EventSubscriber/
│   │   └── ApiExceptionSubscriber.php
│   ├── Repository/
│   │   └── TaskRepository.php
│   └── Service/
│       └── TaskService.php
├── tests/
│   ├── Integration/
│   │   └── TaskApiTest.php
│   └── Unit/
│       ├── TaskServiceTest.php
│       └── TaskTest.php
├── .env                    # Default environment variables
├── .env.example            # Environment variables template
├── .env.dev                # Dev-specific defaults
├── .env.test               # Test-specific defaults
├── composer.json
└── phpunit.dist.xml
```

## Main Entity

| Entity | Description |
|---|---|
| Task | Represents a task. Contains title, description, status, priority, due_date, created_at and updated_at |

## API Endpoints

Local base URL: `http://localhost:8000`

### Tasks (`/api/tasks`)

| Method | Route | Description |
|---|---|---|
| GET | /api/tasks | List all tasks |
| GET | /api/tasks/{id} | Find a task by ID |
| POST | /api/tasks | Create a new task |
| PUT | /api/tasks/{id} | Fully update a task |
| PATCH | /api/tasks/{id} | Partially update a task |
| PATCH | /api/tasks/{id}/status | Update task status |
| DELETE | /api/tasks/{id} | Delete a task |

### Response Codes

| Code | Situation |
|---|---|
| 200 | Success |
| 201 | Task created |
| 204 | Task deleted |
| 400 | Invalid input (bad priority or status value) |
| 404 | Task not found |
| 422 | Validation error or invalid status transition |

## Status Transitions

Tasks follow a defined state machine:

```
pending -> in_progress -> completed
pending -> cancelled
in_progress -> cancelled
```

Transitions not listed above are rejected with a 422 response.

### Priority Levels

`low` | `medium` (default) | `high` | `urgent`

## How to Run

### Prerequisites

- PHP 8.3+ with `pdo_pgsql` extension enabled
- PostgreSQL 16 installed and running
- Composer installed

### 1) Clone and access the project

```bash
git clone https://github.com/jhowzluk/todo-api-symfony.git
cd todo-api-symfony
```

### 2) Install dependencies

```bash
composer install
```

### 3) Set up environment variables

```bash
cp .env.example .env.local
```

Edit `.env.local` with your database credentials.

### 4) Create the database and run migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 5) Start the development server

```bash
symfony server:start
```

The API will be available at `http://localhost:8000`.

## Running Tests

### 1) Set up the test database

Create a `.env.test.local` file with your test database credentials:

```env
POSTGRES_USER=your_user
POSTGRES_PASS=your_password
POSTGRES_PORT=5432
```

Then create the test database:

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

### 2) Run the tests

```bash
php bin/phpunit
```
