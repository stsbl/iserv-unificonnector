<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Security;

use IServ\Bundle\Authentication\Authentication\PortalAuthenticatedToken;
use IServ\Library\UserIdentity\Privilege\PrivilegeId;
use IServ\Library\UserToken\AuthenticatedUserTokenInterface;
use IServ\Library\UserToken\Stamps\Stamp\AdminAuthenticatedStamp;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class AdminAuthenticatedVoter extends Voter
{
    public const ATTR_IS_ADMIN = 'unificonnector-admin';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ATTR_IS_ADMIN === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token instanceof PortalAuthenticatedToken) {
            return false;
        }

        $userToken = $token->getUserToken();
        if (!$userToken instanceof AuthenticatedUserTokenInterface || !$userToken->getUser()->getPrivileges()->contains(PrivilegeId::createFromString(Privileges::ADMIN))) {
            return false;
        }

        $stamp = $userToken->getStamps()->get(AdminAuthenticatedStamp::class);

        return $stamp instanceof AdminAuthenticatedStamp && $stamp->isAdmin();
    }
}
