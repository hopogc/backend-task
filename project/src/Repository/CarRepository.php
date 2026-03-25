<?php

namespace App\Repository;

class CarRepository
{
    private string $filePath;

    public function __construct(string $projectDir, string $carsFile = 'cars.json')
    {
        $this->filePath = $projectDir . '/db/' . $carsFile;
    }

    public function findAll(): array
    {
        return json_decode(file_get_contents($this->filePath), true) ?? [];
    }

    public function findById(int $id): ?array
    {
        foreach ($this->findAll() as $car) {
            if ($car['id'] === $id) {
                return $car;
            }
        }

        return null;
    }

    public function save(array $car): array
    {
        $cars = $this->findAll();

        $maxId = 0;
        foreach ($cars as $existing) {
            if ($existing['id'] > $maxId) {
                $maxId = $existing['id'];
            }
        }

        $car['id'] = $maxId + 1;
        $cars[] = $car;

        $this->persist($cars);

        return $car;
    }

    public function delete(int $id): bool
    {
        $cars = $this->findAll();
        $filtered = array_values(array_filter($cars, fn($c) => $c['id'] !== $id));

        if (count($filtered) === count($cars)) {
            return false;
        }

        $this->persist($filtered);

        return true;
    }

    private function persist(array $cars): void
    {
        file_put_contents($this->filePath, json_encode($cars, JSON_PRETTY_PRINT));
    }
}
