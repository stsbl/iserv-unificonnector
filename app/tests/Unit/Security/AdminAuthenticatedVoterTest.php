<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Security;

use IServ\Bundle\Module\Authorization\AdminVoter;
use IServ\UnifiConnector\Security\AdminAuthenticatedVoter;
use IServ\UnifiConnector\Security\Privileges;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

#[CoversClass(AdminAuthenticatedVoter::class)]
final class AdminAuthenticatedVoterTest extends TestCase
{
    public function testRequiresAdminAuthenticationAndModulePrivilege(): void
    {
        $decisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $attributes = [
            [AdminVoter::AUTHENTICATED_AS_ADMIN],
            [Privileges::PRIV_ADMIN],
        ];
        $invocation = 0;
        $decisionManager->expects(self::exactly(2))
            ->method('decide')
            ->willReturnCallback(static function (TokenInterface $actualToken, array $actualAttributes) use ($token, $attributes, &$invocation): bool {
                self::assertSame($token, $actualToken);
                self::assertSame($attributes[$invocation], $actualAttributes);
                ++$invocation;

                return true;
            });

        $voter = new AdminAuthenticatedVoter($decisionManager);

        self::assertTrue($voter->vote($token, null, [AdminAuthenticatedVoter::ATTR_IS_ADMIN]) > 0);
    }

    public function testRejectsWhenAdminAuthenticationIsMissing(): void
    {
        $decisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $decisionManager->expects(self::once())
            ->method('decide')
            ->with($token, [AdminVoter::AUTHENTICATED_AS_ADMIN])
            ->willReturn(false);

        $voter = new AdminAuthenticatedVoter($decisionManager);

        self::assertSame(-1, $voter->vote($token, null, [AdminAuthenticatedVoter::ATTR_IS_ADMIN]));
    }
}
