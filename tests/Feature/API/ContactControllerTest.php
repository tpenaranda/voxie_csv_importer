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

    public function testDataIsSplitAndPersistedIntoContactsTableAndCustomAtrributesTable()
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
                'custom_field' => 'test',
            ]
        ];

        $response = $this->json('POST', '/api/contacts', compact('data'));

        $this->assertDatabaseHas('contacts', $data[0]);
        $this->assertDatabaseHas('contacts', $data[1]);

        $this->assertDatabaseMissing('contacts', ['custom_field' => 'test']);
        $this->assertDatabaseHas('custom_attributes', ['key' => 'custom_field', 'value' => 'test']);

        $response->assertSuccessful()->assertJson([$data[0], $data[1]]);
    }

    public function testPhoneKeyIsRequired()
    {
        $data = [
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ],
            [
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ],
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ]
        ];

        $response = $this->json('POST', '/api/contacts', compact('data'))->assertStatus(422);;
    }

    public function testTeamIdKeyIsRequired()
    {
        $data = [
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ],
            [
                'phone' => '123',
                'unsubscribed_status' => 'active',
            ],
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ]
        ];

        $response = $this->json('POST', '/api/contacts', compact('data'))->assertStatus(422);;
    }

    public function testUnsubscribedStatusKeyIsRequired()
    {
        $data = [
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ],
            [
                'phone' => '123',
                'team_id' => '456',
            ],
            [
                'phone' => '123',
                'team_id' => '456',
                'unsubscribed_status' => 'active',
            ]
        ];

        $response = $this->json('POST', '/api/contacts', compact('data'))->assertStatus(422);;
    }
}
