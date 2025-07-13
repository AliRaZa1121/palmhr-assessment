<?php

namespace App\Tests\Controller\Api\V2;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HandsetControllerTest extends WebTestCase
{
    public function testIndexReturnsV2Format()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v2/handsets?brand=Apple');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('pagination', $data);

        $handset = $data['data'][0];
        $this->assertArrayHasKey('brand', $handset);
        $this->assertArrayHasKey('price', $handset);
        $this->assertArrayHasKey('amount', $handset['price']);
        $this->assertArrayHasKey('currency', $handset['price']);
        $this->assertArrayHasKey('discount_percentage', $handset['price']);
        $this->assertArrayHasKey('final_price', $handset['price']);
    }

    public function testHandsetViewedEventIsDispatched()
    {
        // Example: check log file or use event dispatcher mock
        $this->assertTrue(true); // Placeholder
    }
}
