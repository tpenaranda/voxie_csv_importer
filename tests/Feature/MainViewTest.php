<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainViewTest extends TestCase
{
    public function testMainViewReturnsOK()
    {
        $this->get('/')->assertSuccessful()->assertViewIs('main')->assertSeeText('Voxie Test');
    }
}
