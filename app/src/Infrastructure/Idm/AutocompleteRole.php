<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Idm;

/** Read-only representation of an IDM role used by the administration autocomplete. */
final readonly class AutocompleteRole
{
    public function __construct(public string $uuid, public ?string $role, public ?string $name, public ?string $module)
    {
    }

    /** @param array<array-key, mixed> $role */
    public static function fromApiResponse(array $role): ?self
    {
        $uuid = self::stringOrNull($role['hexUuid'] ?? null);

        return null !== $uuid && '' !== $uuid
            ? new self(
                $uuid,
                self::stringOrNull($role['role'] ?? null),
                self::stringOrNull($role['name'] ?? null),
                self::stringOrNull($role['module'] ?? null),
            )
            : null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    public function displayName(): string
    {
        return $this->name ?? $this->role ?? $this->uuid;
    }
}
