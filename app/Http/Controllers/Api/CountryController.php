<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $countries = Country::query()
            ->active()
            ->get([
                'id',
                'iso2',
                'iso3',
                'numcode',
                'un_member',
                'calling_code',
                'cctld',
                'short_names',
                'long_names',
                'is_active',
            ])
            ->map(function (Country $country): array {
                return [
                    'id' => (int) $country->id,
                    'iso2' => (string) $country->iso2,
                    'iso3' => $country->iso3 !== null ? (string) $country->iso3 : null,
                    'numcode' => $country->numcode !== null ? (string) $country->numcode : null,
                    'un_member' => $country->un_member !== null ? (string) $country->un_member : null,
                    'calling_code' => $country->calling_code !== null ? (string) $country->calling_code : null,
                    'cctld' => $country->cctld !== null ? (string) $country->cctld : null,
                    'short_name' => $country->short_name,
                    'long_name' => $country->long_name,
                ];
            })
            ->sortBy(fn (array $row) => mb_strtolower((string) ($row['short_name'] ?? '')))
            ->values()
            ->all();

        return $this->response($countries);
    }
}
