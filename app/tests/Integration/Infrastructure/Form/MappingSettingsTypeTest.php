<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration\Infrastructure\Form;

use IServ\UnifiConnector\Application\Mapping\MappingSettings;
use IServ\UnifiConnector\Infrastructure\Form\MappingSettingsType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

#[CoversClass(MappingSettingsType::class)]
final class MappingSettingsTypeTest extends KernelTestCase
{
    public function testBuildsMappingFormWithOptionalAutocompleteSubjects(): void
    {
        self::bootKernel();
        $form = self::getContainer()->get(FormFactoryInterface::class)->create(MappingSettingsType::class, new MappingSettings());

        self::assertSame(MappingSettings::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('id'));
        self::assertTrue($form->has('name'));
        self::assertTrue($form->has('priority'));
        self::assertTrue($form->has('subjects'));
    }
}
