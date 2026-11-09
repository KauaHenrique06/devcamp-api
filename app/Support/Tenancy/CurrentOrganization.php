<?php

namespace App\Support\Tenancy;

use App\Models\Organization;

/**
 * Guarda a organização da requisição atual (o "tenant").
 * É registrada como `scoped` no container: zera a cada requisição.
 */
class CurrentOrganization
{
    private ?Organization $organization = null;

    public function set(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function get(): ?Organization
    {
        return $this->organization;
    }

    public function id(): ?string
    {
        return $this->organization?->getKey();
    }

    public function check(): bool
    {
        return $this->organization !== null;
    }
}
