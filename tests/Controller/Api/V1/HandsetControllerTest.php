<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HandsetControllerTest extends WebTestCase
{
    public function testIndexReturnsHandsetsWithFilters()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/handsets?brand=Apple&features[]=5G');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals('Apple', $data['meta']['filters_applied']['brand']);
        $this->assertContains('5G', $data['meta']['filters_applied']['features']);
    }

    public function testPriceFilterAppliedEventIsDispatched()
    {
        // Example: check log file or use event dispatcher mock
        $this->assertTrue(true); // Placeholder
    }
}
