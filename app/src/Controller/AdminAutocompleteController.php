<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Controller;

use IServ\Library\IdmApiClient\Hydrator\RawHydrator;
use IServ\Library\IdmApiClient\IdmClientInterface;
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
    public function autocomplete(Request $request, IdmClientInterface $idm): JsonResponse
    {
        $this->denyAccessUnlessGranted(AdminAuthenticatedVoter::ATTR_IS_ADMIN);
        if ($request->query->has('values')) {
            return new JsonResponse($this->lookup((string) $request->query->get('values'), $idm));
        }
        $query = trim((string) $request->query->get('query', ''));
        if ('' === $query) {
            return new JsonResponse([]);
        }

        $types = explode(',', (string) $request->query->get('type', ''));
        $suggestions = [];
        if (in_array('userid', $types, true)) {
            foreach (['user', 'firstname', 'lastname'] as $field) {
                foreach ($idm->performRequest('GET', sprintf('/users?%s[icontains]=%s&deleted=false&_attributes=hexUuid,user,firstname,lastname', $field, rawurlencode($query)), new RawHydrator()) as $user) {
                    $id = $user['hexUuid'] ?? null;
                    if (!is_string($id)) {
                        continue;
                    }
                    $suggestions['userid:' . $id] = [
                        'label' => trim(implode(' ', array_filter([(string) ($user['firstname'] ?? ''), (string) ($user['lastname'] ?? '')]))) ?: (string) ($user['user'] ?? $id),
                        'value' => 'userid:' . $id,
                        'source' => 'userid',
                    ];
                }
            }
        }
        if (in_array('groupid', $types, true)) {
            foreach ($idm->performRequest('GET', sprintf('/groups?group[icontains]=%s&_attributes=hexUuid,group', rawurlencode($query)), new RawHydrator()) as $group) {
                $id = $group['hexUuid'] ?? null;
                if (!is_string($id)) {
                    continue;
                }
                $suggestions['groupid:' . $id] = [
                    'label' => (string) ($group['group'] ?? $id),
                    'value' => 'groupid:' . $id,
                    'source' => 'groupid',
                ];
            }
        }

        return new JsonResponse(array_values($suggestions));
    }

    /** @return list<array{label: string, value: string, source: string}> */
    private function lookup(string $values, IdmClientInterface $idm): array
    {
        $suggestions = [];
        foreach (explode(',', $values) as $value) {
            [$source, $id] = array_pad(explode(':', $value, 2), 2, null);
            if (!is_string($id) || !in_array($source, ['userid', 'groupid'], true)) {
                continue;
            }
            $resource = 'userid' === $source ? 'users' : 'groups';
            $attributes = 'userid' === $source ? 'hexUuid,user,firstname,lastname' : 'hexUuid,group';
            $item = $idm->performRequest('GET', sprintf('/%s/%s?_attributes=%s', $resource, rawurlencode($id), $attributes), new RawHydrator());
            $label = 'userid' === $source
                ? trim(implode(' ', array_filter([(string) ($item['firstname'] ?? ''), (string) ($item['lastname'] ?? '')]))) ?: (string) ($item['user'] ?? $id)
                : (string) ($item['group'] ?? $id);
            $suggestions[] = ['label' => $label, 'value' => $source . ':' . $id, 'source' => $source];
        }

        return $suggestions;
    }
}
