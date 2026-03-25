<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CarControllerTest extends WebTestCase
{
    private string $testDbPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDbPath = dirname(__DIR__, 2) . '/db/cars.test.json';
        file_put_contents($this->testDbPath, json_encode([]));
    }

    protected function tearDown(): void
    {
        file_put_contents($this->testDbPath, json_encode([]));
        parent::tearDown();
    }

    // --- GET /api/cars ---

    public function testListCarsReturnsEmptyArray(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testListCarsReturnsSavedCars(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Toyota', 'model' => 'Yaris', 'build_date' => '2023-01-01', 'colour_id' => 1])
        );

        $client->request('GET', '/api/cars');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('Toyota', $data[0]['make']);
    }

    // --- POST /api/cars ---

    public function testCreateCarReturns201(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'build_date' => '2023-06-15', 'colour_id' => 2])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Ford', $data['make']);
        $this->assertSame('Focus', $data['model']);
        $this->assertSame('2023-06-15', $data['build_date']);
        $this->assertSame(2, $data['colour_id']);
    }

    public function testCreateCarAutoIncrementsId(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'BMW', 'model' => 'X5', 'build_date' => '2023-01-01', 'colour_id' => 4])
        );
        $first = json_decode($client->getResponse()->getContent(), true);

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Audi', 'model' => 'A3', 'build_date' => '2023-01-01', 'colour_id' => 1])
        );
        $second = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($first['id'] + 1, $second['id']);
    }

    public function testCreateCarMissingMakeReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['model' => 'Focus', 'build_date' => '2023-06-15', 'colour_id' => 1])
        );

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCreateCarMissingModelReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'build_date' => '2023-06-15', 'colour_id' => 1])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateCarMissingBuildDateReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'colour_id' => 1])
        );

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('build_date', $data['errors'][0]);
    }

    public function testCreateCarBuildDateTooOldReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'build_date' => '2010-01-01', 'colour_id' => 1])
        );

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('4 years', $data['errors'][0]);
    }

    public function testCreateCarInvalidBuildDateFormatReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'build_date' => '15-06-2023', 'colour_id' => 1])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateCarInvalidColourIdReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'build_date' => '2023-06-15', 'colour_id' => 99])
        );

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('colour_id', $data['errors'][0]);
    }

    public function testCreateCarMissingColourIdReturns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Ford', 'model' => 'Focus', 'build_date' => '2023-06-15'])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    // --- GET /api/car/{id} ---

    public function testGetCarReturns200(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Honda', 'model' => 'Civic', 'build_date' => '2023-03-10', 'colour_id' => 3])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/api/car/' . $created['id']);

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($created['id'], $data['id']);
        $this->assertSame('Honda', $data['make']);
    }

    public function testGetCarNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/car/9999');

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    // --- DELETE /api/cars/{id} ---

    public function testDeleteCarReturns204(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Skoda', 'model' => 'Octavia', 'build_date' => '2022-11-01', 'colour_id' => 2])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('DELETE', '/api/cars/' . $created['id']);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteCarRemovesItFromList(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['make' => 'Skoda', 'model' => 'Octavia', 'build_date' => '2022-11-01', 'colour_id' => 2])
        );
        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request('DELETE', '/api/cars/' . $created['id']);
        $client->request('GET', '/api/cars');

        $cars = json_decode($client->getResponse()->getContent(), true);
        $ids = array_column($cars, 'id');
        $this->assertNotContains($created['id'], $ids);
    }

    public function testDeleteCarNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/cars/9999');

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}
