<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration;

use IServ\UnifiConnector\Kernel;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(Kernel::class)]
final class KernelTest extends KernelTestCase
{
    public function testTestKernelUsesTheModuleName(): void
    {
        self::bootKernel();

        $method = new \ReflectionMethod(self::$kernel, 'getModule');
        self::assertSame('unificonnector', $method->invoke(self::$kernel));
    }

    public function testConfiguresPortalWebSessionStorageInTestEnvironment(): void
    {
        $kernel = new Kernel('test', true);
        $container = new ContainerBuilder();
        $container->setDefinition('session.storage.factory.mock_file', new Definition());

        $kernel->process($container);

        self::assertSame('IServPortalWebSession', $container->getDefinition('session.storage.factory.mock_file')->getArgument('$name'));
    }

    public function testDoesNotAlterContainerOutsideTestEnvironment(): void
    {
        (new Kernel('prod', false))->process(new ContainerBuilder());

        self::assertTrue(true);
    }
}
