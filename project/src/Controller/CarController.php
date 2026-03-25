<?php

namespace App\Controller;

use App\Repository\CarRepository;
use App\Validator\CarValidator;
use Nelmio\ApiDocBundle\Attribute\Areas;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cars')]
#[Areas(['default'])]
class CarController extends AbstractController
{
    public function __construct(
        private CarRepository $carRepository,
        private CarValidator $carValidator,
    ) {}

    #[Route('/api/cars', name: 'cars_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/cars',
        summary: 'List all cars',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Array of cars',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Car')
                )
            ),
        ]
    )]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->carRepository->findAll());
    }

    #[Route('/api/car/{id}', name: 'car_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/car/{id}',
        summary: 'Get a single car by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Car found',
                content: new OA\JsonContent(ref: '#/components/schemas/Car')
            ),
            new OA\Response(
                response: 404,
                description: 'Car not found',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'error', type: 'string', example: 'Car not found')]
                )
            ),
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $car = $this->carRepository->findById($id);

        if ($car === null) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($car);
    }

    #[Route('/api/cars', name: 'cars_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/cars',
        summary: 'Create a new car',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CarInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Car created',
                content: new OA\JsonContent(ref: '#/components/schemas/Car')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        ),
                    ]
                )
            ),
        ]
    )]
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
    #[OA\Delete(
        path: '/api/cars/{id}',
        summary: 'Delete a car by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Car deleted'),
            new OA\Response(
                response: 404,
                description: 'Car not found',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'error', type: 'string', example: 'Car not found')]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        if (!$this->carRepository->delete($id)) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
