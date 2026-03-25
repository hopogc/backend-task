<?php

namespace App\Controller;

use App\Repository\CarRepository;
use App\Validator\CarValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CarController extends AbstractController
{
    public function __construct(
        private CarRepository $carRepository,
        private CarValidator $carValidator,
    ) {}

    #[Route('/api/cars', name: 'cars_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->carRepository->findAll());
    }

    #[Route('/api/car/{id}', name: 'car_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $car = $this->carRepository->findById($id);

        if ($car === null) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($car);
    }

    #[Route('/api/cars', name: 'cars_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->carValidator->validate($data);

        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $car = $this->carRepository->save([
            'make'       => $data['make'],
            'model'      => $data['model'],
            'build_date' => $data['build_date'],
            'colour_id'  => $data['colour_id'],
        ]);

        return new JsonResponse($car, Response::HTTP_CREATED);
    }

    #[Route('/api/cars/{id}', name: 'cars_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        if (!$this->carRepository->delete($id)) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
