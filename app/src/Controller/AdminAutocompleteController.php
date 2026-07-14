<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Controller;

use IServ\Bundle\IdmDataBroker\Contract\IdmGroupFetcher;
use IServ\Bundle\IdmDataBroker\Contract\IdmUserFetcher;
use IServ\Library\Avatar\AvatarSize;
use IServ\Library\Avatar\Renderer\AvatarRendererInterface;
use IServ\Library\Avatar\Renderer\AvatarRenderStyle;
use IServ\Library\Avatar\UrlGenerator\AvatarPlaceholderStyle;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteGroup;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteUser;
use IServ\UnifiConnector\Security\AdminAuthenticatedVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Full-directory autocomplete restricted to this module's authenticated administrators. */
#[Route('/admin/unificonnector/api/autocomplete')]
final class AdminAutocompleteController extends AbstractController
{
    #[Route('', name: 'unificonnector_admin_autocomplete', methods: ['GET'])]
    public function autocomplete(Request $request, IdmUserFetcher $users, IdmGroupFetcher $groups, AvatarRendererInterface $avatars): JsonResponse
    {
        $this->denyAccessUnlessGranted(AdminAuthenticatedVoter::ATTR_IS_ADMIN);
        $types = explode(',', (string) $request->query->get('type', ''));

        if ($request->query->has('values')) {
            return new JsonResponse($this->lookup((string) $request->query->get('values'), $users, $groups, $avatars));
        }

        $query = trim((string) $request->query->get('query', ''));
        if ('' === $query) {
            return new JsonResponse([]);
        }

        $suggestions = [];
        if (in_array('userid', $types, true)) {
            foreach (['user', 'firstname', 'lastname'] as $field) {
                foreach ($users->getFilteredUsers([$field . '[icontains]' => $query, 'deleted' => 'false'], AutocompleteUser::class) as $user) {
                    $suggestions['userid:' . $user->uuid] = self::userSuggestion($user, $avatars);
                }
            }
        }
        if (in_array('groupid', $types, true)) {
            foreach ($groups->getFilteredGroups(['name[icontains]' => $query], AutocompleteGroup::class) as $group) {
                $suggestions['groupid:' . $group->uuid] = self::groupSuggestion($group, $avatars);
            }
        }

        return new JsonResponse(array_values($suggestions));
    }

    /** @return list<array{label: string, value: string, source: string, avatarHtml: string, extra: string}> */
    private function lookup(string $values, IdmUserFetcher $users, IdmGroupFetcher $groups, AvatarRendererInterface $avatars): array
    {
        $suggestions = [];
        foreach (explode(',', $values) as $value) {
            [$source, $id] = array_pad(explode(':', $value, 2), 2, null);
            if (!is_string($id) || !in_array($source, ['userid', 'groupid'], true)) {
                continue;
            }
            $uuid = Uuid::createFromString($id);
            $item = 'userid' === $source
                ? $users->getUser($uuid, AutocompleteUser::class)
                : $groups->getGroup($uuid, AutocompleteGroup::class);
            if ($item instanceof AutocompleteUser) {
                $suggestions[] = self::userSuggestion($item, $avatars);
            } elseif ($item instanceof AutocompleteGroup) {
                $suggestions[] = self::groupSuggestion($item, $avatars);
            }
        }

        return $suggestions;
    }

    /** @return array{label: string, value: string, source: string, avatarHtml: string, extra: string} */
    private static function userSuggestion(AutocompleteUser $user, AvatarRendererInterface $avatars): array
    {
        $name = $user->displayName();

        return [
            'label' => $name,
            'value' => 'userid:' . $user->uuid,
            'source' => 'userid',
            'avatarHtml' => $avatars->render(Uuid::createFromNormalized($user->uuid), AvatarSize::default(), AvatarRenderStyle::ROUNDED, $name),
            'extra' => implode(' · ', array_filter([$user->account, $user->auxInfo])),
        ];
    }

    /** @return array{label: string, value: string, source: string, avatarHtml: string, extra: string} */
    private static function groupSuggestion(AutocompleteGroup $group, AvatarRendererInterface $avatars): array
    {
        $name = $group->displayName();
        if ('' === $name) {
            $name = '?';
        }

        return [
            'label' => $name,
            'value' => 'groupid:' . $group->uuid,
            'source' => 'groupid',
            'avatarHtml' => $avatars->renderPlaceholder($name, AvatarSize::default(), AvatarRenderStyle::ROUNDED, AvatarPlaceholderStyle::GROUP),
            'extra' => $group->account ?? '',
        ];
    }
}
