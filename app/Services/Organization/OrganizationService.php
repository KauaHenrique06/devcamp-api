<?php

namespace App\Services\Organization;

use App\Enums\OrganizationRole;
use App\Http\Resources\Organization\OrganizationCollection;
use App\Http\Resources\Organization\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizationService
{
    private const LOGO_DISK = 'public';

    private const LOGO_DIRECTORY = 'organizations/logos';

    public function index(array $data): OrganizationCollection
    {
        $perPage = (int) ($data['per_page'] ?? 10);
        $page = (int) ($data['page'] ?? 1);
        $search = $data['search'] ?? null;

        $paginate = Organization::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereLike('name', "%{$search}%")
                        ->orWhereLike('description', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return new OrganizationCollection($paginate);
    }

    public function show(array $data): OrganizationResource
    {
        return new OrganizationResource(Organization::findOrFail($data['id']));
    }

    public function store(array $data): OrganizationResource
    {
        $logoPath = $this->storeLogo($data['logo'] ?? null);

        try {
            return DB::transaction(function () use ($data, $logoPath): OrganizationResource {
                $organization = Organization::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'logo' => $logoPath,
                ]);

                // Quem cria a organização vira o dono dela.
                $organization->members()->attach(Auth::id(), ['role' => OrganizationRole::OWNER->value]);

                return new OrganizationResource($organization);
            });
        } catch (\Throwable $e) {
            // Se o banco falhar, não deixa o arquivo órfão no disco.
            $this->deleteLogo($logoPath);

            throw $e;
        }
    }

    public function update(array $data): OrganizationResource
    {
        $organization = Organization::findOrFail($data['id']);

        $newLogoPath = $this->storeLogo($data['logo'] ?? null);
        $oldLogoPath = $organization->logo;

        unset($data['id'], $data['logo']);

        if ($newLogoPath) {
            $data['logo'] = $newLogoPath;
        }

        try {
            $resource = DB::transaction(function () use ($organization, $data): OrganizationResource {
                $organization->update($data);

                return new OrganizationResource($organization->refresh());
            });
        } catch (\Throwable $e) {
            $this->deleteLogo($newLogoPath);

            throw $e;
        }

        if ($newLogoPath) {
            $this->deleteLogo($oldLogoPath);
        }

        return $resource;
    }

    public function delete(array $data): void
    {
        $organization = Organization::findOrFail($data['id']);

        DB::transaction(function () use ($organization) {
            $organization->delete();
        });
    }

    private function storeLogo(?UploadedFile $logo): ?string
    {
        return $logo?->store(self::LOGO_DIRECTORY, self::LOGO_DISK) ?: null;
    }

    private function deleteLogo(?string $path): void
    {
        if ($path) {
            Storage::disk(self::LOGO_DISK)->delete($path);
        }
    }
}
