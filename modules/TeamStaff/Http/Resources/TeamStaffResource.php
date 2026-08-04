<?php

namespace Modules\TeamStaff\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profilePicture = (string) ($this->profile_picture ?? '');
        $profilePictureUrl = null;

        if ($profilePicture !== '') {
            $profilePictureUrl = preg_match('/^https?:\/\//i', $profilePicture)
                ? $profilePicture
                : asset('storage/' . ltrim($profilePicture, '/'));
        }

        $files = $this->files;

        if (!is_array($files)) {
            $files = [];
        }

        $files = array_values(array_filter(array_map(static function ($item) {
            if (!is_string($item) || trim($item) === '') {
                return null;
            }

            $path = trim($item);

            return preg_match('/^https?:\/\//i', $path)
                ? $path
                : asset('storage/' . ltrim($path, '/'));
        }, $files)));

        $socialNetworks = $this->social_networks;

        if (!is_array($socialNetworks)) {
            $socialNetworks = [];
        }

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'position' => $this->position,
            'description' => $this->description,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'profile_picture' => $profilePictureUrl,
            'social_networks' => $socialNetworks,
            'files' => $files,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
