<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Infrastructure\Form;

use IServ\UnifiConnector\Infrastructure\Form\DeleteApiKeyActionType;
use IServ\UnifiConnector\Infrastructure\Form\MappingActionType;
use IServ\UnifiConnector\Infrastructure\Form\SyncActionType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(DeleteApiKeyActionType::class)]
#[CoversClass(MappingActionType::class)]
#[CoversClass(SyncActionType::class)]
final class ActionTypesTest extends TypeTestCase
{
    public function testDeleteApiKeyAndSyncFormsContainTheirSubmitAction(): void
    {
        self::assertTrue($this->factory->create(DeleteApiKeyActionType::class)->has('submit'));
        self::assertTrue($this->factory->create(SyncActionType::class)->has('submit'));
    }

    public function testMappingActionIncludesConfiguredDirectionAndIcon(): void
    {
        $form = $this->factory->create(MappingActionType::class, null, [
            'label' => 'Move up',
            'icon' => 'arrow-up',
            'direction' => 'up',
        ]);

        self::assertTrue($form->has('id'));
        self::assertSame('up', $form->get('direction')->getData());
        self::assertSame('arrow-up', $form->get('submit')->getConfig()->getOption('attr')['icon']);
    }

    public function testMappingActionOmitsDirectionWhenItIsNotConfigured(): void
    {
        $form = $this->factory->create(MappingActionType::class, null, ['label' => 'Delete', 'icon' => 'trash']);

        self::assertFalse($form->has('direction'));
    }
}
