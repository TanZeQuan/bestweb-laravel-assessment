<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'BestWeb User Management API',
    version: '1.0.0',
    description: 'API documentation for the BestWeb Laravel Assessment.'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local development server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
class SwaggerDocumentation
{
    #[OA\Get(
        path: '/api/users',
        summary: 'Get user list',
        description: 'Get a paginated list of users with optional status filtering.',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['active', 'inactive']
                )
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 50,
                    default: 10
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User list returned successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid query parameters'
            ),
        ]
    )]
    public function listUsers(): void {}

    #[OA\Post(
        path: '/api/users',
        summary: 'Create a user',
        description: 'Create a new user. Admin access required.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone_number', 'password'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Demo User'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'demo@bestweb.test'
                    ),
                    new OA\Property(
                        property: 'phone_number',
                        type: 'string',
                        example: '01112345678'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'StrongPass123!'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        default: 'active'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed'
            ),
        ]
    )]
    public function createUser(): void {}

    #[OA\Get(
        path: '/api/users/{user}',
        summary: 'Get user details',
        description: 'Get user information by ID. Admin access required.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 2
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User details returned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Test User'),
                                new OA\Property(property: 'email', type: 'string', example: 'testuser@bestweb.test'),
                                new OA\Property(property: 'phone_number', type: 'string', example: '0100000002'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function getUserDetails(): void {}

    #[OA\Patch(
        path: '/api/users/{user}',
        summary: 'Update a user',
        description: 'Update user details. Admin access required.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 2
                )
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Updated Test User'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'updated@bestweb.test'
                    ),
                    new OA\Property(
                        property: 'phone_number',
                        type: 'string',
                        example: '01112345678'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed'
            ),
        ]
    )]
    public function updateUser(): void {}

    #[OA\Delete(
        path: '/api/users/{user}',
        summary: 'Delete a user',
        description: 'Soft delete a user. Admin access required. Admins cannot delete their own account.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 3
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User deleted successfully'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
        ]
    )]
    public function deleteUser(): void {}

    #[OA\Post(
        path: '/api/users/bulk-delete',
        summary: 'Bulk delete users',
        description: 'Soft delete multiple users. Admins cannot delete their own account.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_ids'],
                properties: [
                    new OA\Property(
                        property: 'user_ids',
                        type: 'array',
                        minItems: 1,
                        maxItems: 100,
                        uniqueItems: true,
                        items: new OA\Items(type: 'integer'),
                        example: [6, 7]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Users deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Users deleted successfully'
                        ),
                        new OA\Property(
                            property: 'deleted_count',
                            type: 'integer',
                            example: 2
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed'
            ),
        ]
    )]
    public function bulkDeleteUsers(): void {}

    #[OA\Get(
        path: '/api/users/export',
        summary: 'Export users to Excel',
        description: 'Download active and inactive users as an Excel file. Soft-deleted users are excluded. Admin access required.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Excel file downloaded successfully',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
        ]
    )]
    public function exportUsers(): void {}
}
