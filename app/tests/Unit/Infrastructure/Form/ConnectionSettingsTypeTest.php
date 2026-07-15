<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Infrastructure\Form;

use IServ\UnifiConnector\Application\Configuration\ConnectionSettings;
use IServ\UnifiConnector\Infrastructure\Form\ConnectionSettingsType;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(ConnectionSettingsType::class)]
final class ConnectionSettingsTypeTest extends TypeTestCase
{
    private UserGroupRepository&MockObject $userGroups;

    protected function setUp(): void
    {
        $this->userGroups = $this->createMock(UserGroupRepository::class);
        $this->userGroups->method('all')->willReturn([]);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new ConnectionSettingsType($this->userGroups);

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testApiKeySubmissionAcceptsOmittedPasswordCredentials(): void
    {
        $form = $this->factory->create(ConnectionSettingsType::class, new ConnectionSettings());
        $form->submit([
            'url' => 'https://unifi.example.test',
            'authenticationMode' => 'api_key',
            'apiKey' => 'secret',
            'fallbackGroup' => 'Default',
        ]);

        self::assertTrue($form->isSynchronized());
        /** @var ConnectionSettings $settings */
        $settings = $form->getData();
        self::assertSame('', $settings->username);
        self::assertSame('', $settings->password);
    }
}
