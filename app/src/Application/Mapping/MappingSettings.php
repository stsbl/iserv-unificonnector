<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Application\Mapping;

/** Data entered when an administrator creates a UniFi group mapping. */
final class MappingSettings
{
    public string $id = '';
    public string $name = '';
    public int $priority = 0;

    /** @var list<object> AutocompleteTagsData values supplied by the form. */
    public array $subjects = [];
}
