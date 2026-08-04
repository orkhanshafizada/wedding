<?php
namespace App\OpenApi\Schemas;

/**
 * @OA\Schema(
 *   schema="Menu",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Main"),
 *   @OA\Property(property="slug", type="string", example="main"),
 *   @OA\Property(property="status", type="boolean", example=true),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="StoreMenuRequest",
 *   type="object",
 *   required={"name","status"},
 *   @OA\Property(property="name", type="string", maxLength=255, example="Header"),
 *   @OA\Property(property="slug", type="string", nullable=true, example="header"),
 *   @OA\Property(property="status", type="boolean", example=true),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *   schema="UpdateMenuRequest",
 *   type="object",
 *   required={"name","status"},
 *   @OA\Property(property="name", type="string", maxLength=255, example="Header"),
 *   @OA\Property(property="slug", type="string", nullable=true, example="header"),
 *   @OA\Property(property="status", type="boolean", example=true),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null)
 * )
 */
final class Menu {}
