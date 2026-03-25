<?php

namespace App\Repository;

class CarRepository
{
    private readonly string $filePath;

    public function __construct(string $projectDir, string $carsFile = 'cars.json')
    {
        $this->filePath = $projectDir . '/db/' . $carsFile;
    }

    public function findAll(): array
    {
        return json_decode(file_get_contents($this->filePath), true, flags: JSON_THROW_ON_ERROR) ?? [];
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

        $car['id'] = max([0, ...array_column($cars, 'id')]) + 1;
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
        file_put_contents($this->filePath, json_encode($cars, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }
}
