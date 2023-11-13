<?php

namespace Tests\Unit;

use Tests\TestCase as TestCase;

class TodoTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_get_all_items(): void
    {
        $response = $this->get('/api/items');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'completed',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_get_filtered_items(): void
    {
        $response = $this->get('/api/items?completed=1&name=lorem');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'completed',
                'created_at',
                'updated_at',
            ],
        ]);

        $response = $this->get('/api/items?completed=1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'completed',
                'created_at',
                'updated_at',
            ],
        ]);

        $response = $this->get('/api/items?name=lorem');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'completed',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_get_paginated_items(): void
    {
        $response = $this->get('/api/items?page=1&per_page=5');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'completed',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_get_one_item(): void
    {
        $response = $this->get('/api/items/101');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'completed',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_update_item(): void
    {
        $response = $this->put('/api/items/101', [
            'name' => 'Lorem ipsum dolor sit amet',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla euismod, nisl eget aliquam ultricies, nunc nisl aliquet nunc, quis aliquam nisl nunc eget nunc. Sed vitae nisl eget nisl aliquet aliquam. Sed vitae nisl eget nisl aliquet aliquam.',
            'completed' => 1,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'completed',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_delete_item(): void
    {
        $response = $this->delete('/api/items/101');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
