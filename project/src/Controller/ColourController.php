<?php

namespace App\Controller;

use App\Repository\ColourRepository;
use Nelmio\ApiDocBundle\Attribute\Areas;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Colours')]
#[Areas(['default'])]
class ColourController extends AbstractController
{
    public function __construct(
        private readonly ColourRepository $colourRepository,
    ) {}

    #[Route('/api/colours', name: 'colours_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/colours',
        summary: 'List all colours',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Array of colours',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Colour')
                )
            ),
        ]
    )]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->colourRepository->findAll());
    }

    #[Route('/api/colours', name: 'colours_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/colours',
        summary: 'Create a new colour',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ColourInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Colour created',
                content: new OA\JsonContent(ref: '#/components/schemas/Colour')
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

        $errors = $this->validate($data);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $colour = $this->colourRepository->save(trim($data['name']));

        return new JsonResponse($colour, Response::HTTP_CREATED);
    }

    #[Route('/api/colours/{id}', name: 'colours_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/colours/{id}',
        summary: 'Update a colour by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ColourInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Colour updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Colour')
            ),
            new OA\Response(
                response: 404,
                description: 'Colour not found',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'error', type: 'string', example: 'Colour not found')]
                )
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
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validate($data);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $colour = $this->colourRepository->update($id, trim($data['name']));

        if ($colour === null) {
            return new JsonResponse(['error' => 'Colour not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($colour);
    }

    #[Route('/api/colours/{id}', name: 'colours_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/colours/{id}',
        summary: 'Delete a colour by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Colour deleted'),
            new OA\Response(
                response: 404,
                description: 'Colour not found',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'error', type: 'string', example: 'Colour not found')]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        if (!$this->colourRepository->delete($id)) {
            return new JsonResponse(['error' => 'Colour not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name']) || !is_string($data['name']) || trim($data['name']) === '') {
            $errors[] = 'name is required and must be a non-empty string';
        }

        return $errors;
    }
}
