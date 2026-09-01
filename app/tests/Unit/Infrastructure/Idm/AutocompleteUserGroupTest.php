<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Infrastructure\Idm;

use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteGroup;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AutocompleteUser::class)]
#[CoversClass(AutocompleteGroup::class)]
final class AutocompleteUserGroupTest extends TestCase
{
    public function testUsesPersonNameAndFallsBackToAccountOrUuid(): void
    {
        self::assertSame('Ada Lovelace', (new AutocompleteUser('uuid', 'ada', 'Ada', 'Lovelace', null))->displayName());
        self::assertSame('ada', (new AutocompleteUser('uuid', 'ada', null, null, null))->displayName());
        self::assertSame('uuid', (new AutocompleteUser('uuid', null, null, null, null))->displayName());
    }

    public function testUsesGroupNameAndFallsBackToAccountOrUuid(): void
    {
        self::assertSame('Teachers', (new AutocompleteGroup('uuid', 'Teachers', 'teachers'))->displayName());
        self::assertSame('teachers', (new AutocompleteGroup('uuid', null, 'teachers'))->displayName());
        self::assertSame('uuid', (new AutocompleteGroup('uuid', null, null))->displayName());
    }
}
