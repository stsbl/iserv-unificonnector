<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Functional\Controller;

use IServ\Bundle\TestBrowser\Test\TestBrowser;
use IServ\Bundle\IdmDataBroker\Contract\IdmGroupFetcher;
use IServ\Bundle\IdmDataBroker\Contract\IdmUserFetcher;
use IServ\Library\Avatar\Renderer\AvatarRendererInterface;
use IServ\Library\UserToken\Test\User\TestUserBuilder;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Controller\AdminAutocompleteController;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteGroup;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteRole;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteRoleProviderInterface;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteUser;
use IServ\UnifiConnector\Security\Privileges;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(AdminAutocompleteController::class)]
final class AdminAutocompleteControllerTest extends WebTestCase
{
    public function testAutocompleteRequiresTheModulePrivilege(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->request('GET', '/admin/unificonnector/api/autocomplete?query=staff&type=groupid');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAuthenticatedAdminGetsEmptyResponseForAnEmptyQuery(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('GET', '/admin/unificonnector/api/autocomplete?type=userid,groupid,roleid');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[]', (string) $client->getResponse()->getContent());
    }

    public function testAutocompleteReturnsDeduplicatedUserGroupAndRoleSuggestions(): void
    {
        $users = $this->createMock(IdmUserFetcher::class);
        $users->expects(self::exactly(3))->method('getFilteredUsers')->willReturn([
            'user' => new AutocompleteUser('e5897a99-0f09-4563-8a17-93567f7a58b8', 'ada', 'Ada', 'Lovelace', 'Mathematics'),
        ]);
        $groups = $this->createMock(IdmGroupFetcher::class);
        $groups->expects(self::once())->method('getFilteredGroups')->willReturn([
            'group' => new AutocompleteGroup('e9b5542e-4f55-46d9-8cd8-b4ff826d55a4', 'Teachers', 'teachers'),
        ]);
        $roles = $this->createMock(AutocompleteRoleProviderInterface::class);
        $roles->expects(self::once())->method('search')->with('ada')->willReturn([
            new AutocompleteRole('c5ac939a-2d74-4630-af64-7093f0cbd251', 'ROLE_TEACHER', 'Teacher', 'core'),
        ]);
        $avatars = $this->createMock(AvatarRendererInterface::class);
        $avatars->method('render')->willReturn('<img>');
        $avatars->method('renderPlaceholder')->willReturn('<span>');
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(IdmUserFetcher::class, $users);
        self::getContainer()->set(IdmGroupFetcher::class, $groups);
        self::getContainer()->set(AutocompleteRoleProviderInterface::class, $roles);
        self::getContainer()->set(AvatarRendererInterface::class, $avatars);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());

        $client->request('GET', '/admin/unificonnector/api/autocomplete?query=ada&type=userid,groupid,roleid');

        self::assertJsonStringEqualsJsonString(json_encode([
            ['label' => 'Ada Lovelace', 'value' => 'userid:e5897a99-0f09-4563-8a17-93567f7a58b8', 'source' => 'userid', 'avatarHtml' => '<img>', 'extra' => 'ada · Mathematics'],
            ['label' => 'Teachers', 'value' => 'groupid:e9b5542e-4f55-46d9-8cd8-b4ff826d55a4', 'source' => 'groupid', 'avatarHtml' => '<span>', 'extra' => 'teachers'],
            ['label' => 'Teacher', 'value' => 'roleid:c5ac939a-2d74-4630-af64-7093f0cbd251', 'source' => 'roleid', 'avatarHtml' => '', 'extra' => 'ROLE_TEACHER · core'],
        ], JSON_THROW_ON_ERROR), (string) $client->getResponse()->getContent());
    }

    public function testLookupResolvesPersistedSuggestionsAndSkipsInvalidValues(): void
    {
        $users = $this->createMock(IdmUserFetcher::class);
        $users->expects(self::once())->method('getUser')->willReturn(new AutocompleteUser('e5897a99-0f09-4563-8a17-93567f7a58b8', 'ada', null, null, null));
        $groups = $this->createMock(IdmGroupFetcher::class);
        $groups->expects(self::once())->method('getGroup')->willReturn(new AutocompleteGroup('e9b5542e-4f55-46d9-8cd8-b4ff826d55a4', null, 'teachers'));
        $roles = $this->createMock(AutocompleteRoleProviderInterface::class);
        $roles->expects(self::once())->method('get')->with('c5ac939a-2d74-4630-af64-7093f0cbd251')->willReturn(new AutocompleteRole('c5ac939a-2d74-4630-af64-7093f0cbd251', null, null, null));
        $avatars = $this->createMock(AvatarRendererInterface::class);
        $avatars->method('render')->willReturn('<img>');
        $avatars->method('renderPlaceholder')->willReturn('<span>');
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(IdmUserFetcher::class, $users);
        self::getContainer()->set(IdmGroupFetcher::class, $groups);
        self::getContainer()->set(AutocompleteRoleProviderInterface::class, $roles);
        self::getContainer()->set(AvatarRendererInterface::class, $avatars);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());

        $client->request('GET', '/admin/unificonnector/api/autocomplete?values=userid:e5897a99-0f09-4563-8a17-93567f7a58b8,groupid:e9b5542e-4f55-46d9-8cd8-b4ff826d55a4,roleid:c5ac939a-2d74-4630-af64-7093f0cbd251,invalid');

        self::assertJsonStringEqualsJsonString(json_encode([
            ['label' => 'ada', 'value' => 'userid:e5897a99-0f09-4563-8a17-93567f7a58b8', 'source' => 'userid', 'avatarHtml' => '<img>', 'extra' => 'ada'],
            ['label' => 'teachers', 'value' => 'groupid:e9b5542e-4f55-46d9-8cd8-b4ff826d55a4', 'source' => 'groupid', 'avatarHtml' => '<span>', 'extra' => 'teachers'],
            ['label' => 'c5ac939a-2d74-4630-af64-7093f0cbd251', 'value' => 'roleid:c5ac939a-2d74-4630-af64-7093f0cbd251', 'source' => 'roleid', 'avatarHtml' => '', 'extra' => ''],
        ], JSON_THROW_ON_ERROR), (string) $client->getResponse()->getContent());
    }

    public function testAutocompleteUsesPlaceholderForMalformedUnnamedGroup(): void
    {
        $groups = $this->createMock(IdmGroupFetcher::class);
        $groups->expects(self::once())->method('getFilteredGroups')->willReturn([
            'group' => new AutocompleteGroup('', null, null),
        ]);
        $avatars = $this->createMock(AvatarRendererInterface::class);
        $avatars->method('renderPlaceholder')->willReturn('<span>');
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(IdmGroupFetcher::class, $groups);
        self::getContainer()->set(AvatarRendererInterface::class, $avatars);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());

        $client->request('GET', '/admin/unificonnector/api/autocomplete?query=group&type=groupid');

        self::assertJsonStringEqualsJsonString(json_encode([
            ['label' => '?', 'value' => 'groupid:', 'source' => 'groupid', 'avatarHtml' => '<span>', 'extra' => ''],
        ], JSON_THROW_ON_ERROR), (string) $client->getResponse()->getContent());
    }

}
