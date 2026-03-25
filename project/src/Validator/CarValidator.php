<?php

namespace App\Validator;

use App\Repository\ColourRepository;

class CarValidator
{
    public function __construct(private ColourRepository $colourRepository) {}

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['make']) || !is_string($data['make'])) {
            $errors[] = 'make is required and must be a string';
        }

        if (empty($data['model']) || !is_string($data['model'])) {
            $errors[] = 'model is required and must be a string';
        }

        if (empty($data['build_date'])) {
            $errors[] = 'build_date is required';
        } else {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $data['build_date']);
            if (!$date || $date->format('Y-m-d') !== $data['build_date']) {
                $errors[] = 'build_date must be a valid date in Y-m-d format';
            } else {
                $fourYearsAgo = new \DateTimeImmutable('-4 years');
                if ($date < $fourYearsAgo) {
                    $errors[] = 'build_date cannot be older than 4 years';
                }
            }
        }

        if (!isset($data['colour_id'])) {
            $errors[] = 'colour_id is required';
        } elseif (!is_int($data['colour_id'])) {
            $errors[] = 'colour_id must be an integer';
        } elseif ($this->colourRepository->findById($data['colour_id']) === null) {
            $errors[] = 'colour_id is invalid';
        }

        return $errors;
    }
}
