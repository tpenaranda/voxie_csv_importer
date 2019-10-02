<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    public function testPostMethodIsAllowed()
    {
        $this->json('POST', '/api/contacts', ['data' => []])->assertSuccessful();
    }

    public function testGetMethodIsNotAllowed()
    {
        $this->json('GET', '/api/contacts')->assertStatus(405);
    }

    public function testDataKeyIsRequired()
    {
        $this->json('POST', '/api/contacts', [])->assertStatus(422);
    }

    public function testDataIsPersistedIntoContactsTableAndJsonResponseMatches()
    {
        $data = [
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',

            ],
            [
                'first_name' => 'Willy',
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ],
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ]
        ];

        $response = $this->json('POST', '/api/contacts', compact('data'));

        $this->assertDatabaseHas('contacts', $data[0]);
        $this->assertDatabaseHas('contacts', $data[1]);
        $this->assertDatabaseHas('contacts', $data[2]);

        $response->assertSuccessful()->assertJson($data);
    }
}
