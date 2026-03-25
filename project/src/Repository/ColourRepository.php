<?php

namespace App\Repository;

class ColourRepository
{
    private string $filePath;

    public function __construct(string $projectDir)
    {
        $this->filePath = $projectDir . '/db/colours.json';
    }

    public function findAll(): array
    {
        return json_decode(file_get_contents($this->filePath), true);
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
}
