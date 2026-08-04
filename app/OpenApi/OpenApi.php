<?php
namespace App\OpenApi;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="E-commerce API",
 *     description="REST API documentation"
 * )
 * @OA\Server(url="/api/v1", description="API v1")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authorization: Bearer {token}"
 * )
 */
final class OpenApi {}
