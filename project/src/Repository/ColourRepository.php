<?php

namespace App\Repository;

class ColourRepository
{
    private readonly string $filePath;

    public function __construct(string $projectDir)
    {
        $this->filePath = $projectDir . '/db/colours.json';
    }

    public function findAll(): array
    {
        return json_decode(file_get_contents($this->filePath), true, flags: JSON_THROW_ON_ERROR);
    }

    public function findById(int $id): ?array
    {
        foreach ($this->findAll() as $colour) {
            if ($colour['id'] === $id) {
                return $colour;
            }
        }

        return null;
    }

    public function save(string $name): array
    {
        $colours = $this->findAll();

        $colour = [
            'id' => max([0, ...array_column($colours, 'id')]) + 1,
            'name' => $name,
        ];
        $colours[] = $colour;

        $this->persist($colours);

        return $colour;
    }

    public function update(int $id, string $name): ?array
    {
        $colours = $this->findAll();

        foreach ($colours as &$colour) {
            if ($colour['id'] === $id) {
                $colour['name'] = $name;
                $this->persist($colours);
                return $colour;
            }
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $colours = $this->findAll();
        $filtered = array_values(array_filter($colours, fn($c) => $c['id'] !== $id));

        if (count($filtered) === count($colours)) {
            return false;
        }

        $this->persist($filtered);

        return true;
    }

    private function persist(array $colours): void
    {
        file_put_contents($this->filePath, json_encode($colours, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }
}
