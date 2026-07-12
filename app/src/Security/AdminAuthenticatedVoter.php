<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Security;

use IServ\Bundle\Module\Authorization\AdminVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** Requires both an authenticated admin session and the module's privilege. */
final class AdminAuthenticatedVoter extends Voter
{
    public const ATTR_IS_ADMIN = 'unificonnector-admin';

    public function __construct(private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ATTR_IS_ADMIN === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->accessDecisionManager->decide($token, [AdminVoter::AUTHENTICATED_AS_ADMIN])
            && $this->accessDecisionManager->decide($token, [Privileges::PRIV_ADMIN]);
    }
}
