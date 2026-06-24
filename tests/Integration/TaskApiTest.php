<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskApiTest extends WebTestCase
{
    public function testCreateTaskSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'My task', 'description' => 'Desc', 'priority' => 'high'])
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('My task', $data['title']);
        $this->assertSame('Desc', $data['description']);
        $this->assertSame('High', $data['priority']);
        $this->assertSame('Pending', $data['status']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('createdAt', $data);
    }

    public function testCreateTaskWithoutTitleReturns422(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['description' => 'No title'])
        );

        $this->assertSame(422, $client->getResponse()->getStatusCode());
    }

    public function testCreateTaskWithInvalidPriorityReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Task', 'priority' => 'banana'])
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid priority', $data['error']['message']);
    }

    public function testListTasks(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/tasks');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray(json_decode($response->getContent(), true));
    }

    public function testShowTaskReturns200(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Show me'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/api/tasks/' . $created['id']);

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Show me', $data['title']);
    }

    public function testShowNonExistentTaskReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/tasks/99999');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUpdateTaskSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Original'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('PUT', '/api/tasks/' . $created['id'], [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Updated', 'description' => 'New desc', 'priority' => 'urgent'])
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Updated', $data['title']);
        $this->assertSame('New desc', $data['description']);
        $this->assertSame('Urgent', $data['priority']);
    }

    public function testPartialUpdateOnlyChangesProvidedFields(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Original', 'description' => 'Keep this', 'priority' => 'low'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/api/tasks/' . $created['id'], [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Changed'])
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Changed', $data['title']);
        $this->assertSame('Keep this', $data['description']);
        $this->assertSame('Low', $data['priority']);
    }

    public function testUpdateStatusSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Status test'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/api/tasks/' . $created['id'] . '/status', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'in_progress'])
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('In Progress', $data['status']);
    }

    public function testUpdateStatusWithInvalidValueReturns400(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Status test'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/api/tasks/' . $created['id'] . '/status', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'invalid'])
        );

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testUpdateStatusWithInvalidTransitionReturns422(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Transition test'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/api/tasks/' . $created['id'] . '/status', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'completed'])
        );

        $this->assertSame(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateStatusOfNonExistentTaskReturns404(): void
    {
        $client = static::createClient();

        $client->request('PATCH', '/api/tasks/99999/status', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'in_progress'])
        );

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteTaskReturns204(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'To delete'])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('DELETE', '/api/tasks/' . $created['id']);

        $this->assertSame(204, $client->getResponse()->getStatusCode());
    }

    public function testDeleteNonExistentTaskReturns404(): void
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/tasks/99999');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
