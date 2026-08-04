<?php

namespace App\Http\Controllers\Api;

use App\Support\GuestToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GuestTokenController extends BaseApiController
{
    public function create(Request $request): JsonResponse
    {
        if ($request->user('customer-api') !== null) {
            return $this->fail(
                __('Authenticated user does not need a guest token.'),
                422,
                [],
                []
            );
        }

        $token = GuestToken::fromRequest($request);

        if (! is_string($token) || trim($token) === '') {
            $token = GuestToken::generate();
        }

        return $this->created([
            'token' => $token,
        ], __('Guest token created'));
    }
}
