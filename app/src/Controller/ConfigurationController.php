<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Controller;

use IServ\Bundle\AdminIntegration\Controller\AbstractAdminController;
use IServ\Bundle\AdminIntegration\Menu\AdminBreadcrumbs;
use IServ\Bundle\TranslationGettext\Asset\TranslationAssetLoader;
use IServ\Library\ModuleResponse\ResponseContent;
use IServ\UnifiConnector\Application\Configuration\ConnectionSettings;
use IServ\UnifiConnector\Application\Mapping\MappingSettings;
use IServ\UnifiConnector\Application\Mapping\MappingManager;
use IServ\UnifiConnector\Configuration\ConnectionConfiguration;
use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Infrastructure\Form\ConnectionSettingsType;
use IServ\UnifiConnector\Infrastructure\Form\MappingActionType;
use IServ\UnifiConnector\Infrastructure\Form\MappingSettingsType;
use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;
use IServ\UnifiConnector\Security\AdminAuthenticatedVoter;
use IServ\UnifiConnector\Synchronisation\SyncRunner;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/unificonnector')]
final class ConfigurationController extends AbstractAdminController
{
    #[Route('/', name: 'unificonnector_configuration', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        FileConfigurationRepository $configurationRepository,
        UniFiGroupMappingRepository $mappings,
        MappingManager $mappingManager,
        SyncRunner $syncRunner,
        AdminBreadcrumbs $breadcrumbs,
        Packages $packages,
        TranslationAssetLoader $translationAssets,
        FormFactoryInterface $forms,
    ): ResponseContent|RedirectResponse {
        $this->denyAccessUnlessGranted(AdminAuthenticatedVoter::ATTR_IS_ADMIN);

        $connectionForm = $this->createConnectionForm($configurationRepository, $request);
        if ($connectionForm->isSubmitted() && $connectionForm->isValid()) {
            /** @var ConnectionSettings $settings */
            $settings = $connectionForm->getData();
            $configurationRepository->store(new ConnectionConfiguration($settings->url, $settings->username, $settings->password, $settings->fallbackGroup));
            $syncRunner->run();
            $this->addFlash('success', _('Configuration saved and synchronization started.'));

            return $this->redirectToRoute('unificonnector_configuration');
        }

        $mappingForm = $this->createForm(MappingSettingsType::class, new MappingSettings());
        $mappingForm->handleRequest($request);
        if ($mappingForm->isSubmitted() && $mappingForm->isValid()) {
            /** @var MappingSettings $settings */
            $settings = $mappingForm->getData();
            $mappingManager->create($settings);
            $this->addFlash('success', _('Mapping saved.'));

            return $this->redirectToRoute('unificonnector_configuration');
        }

        $mappingViews = [];
        foreach ($mappings->findBy([], ['priority' => 'ASC']) as $mapping) {
            $mappingViews[] = [
                'mapping' => $mapping,
                'moveUpForm' => $this->createActionForm($forms, 'move_up_' . $mapping->id(), $mapping->id(), _('Move up'), 'arrow-up', 'up', $request),
                'moveDownForm' => $this->createActionForm($forms, 'move_down_' . $mapping->id(), $mapping->id(), _('Move down'), 'arrow-down', 'down', $request),
                'deleteForm' => $this->createActionForm($forms, 'delete_' . $mapping->id(), $mapping->id(), _('Delete'), 'trash', null, $request),
            ];
        }
        foreach ($mappingViews as $item) {
            foreach (['moveUpForm', 'moveDownForm', 'deleteForm'] as $formName) {
                $actionForm = $item[$formName];
                if ($actionForm->isSubmitted() && $actionForm->isValid()) {
                    $mapping = $mappings->find($actionForm->get('id')->getData());
                    if ($mapping instanceof UniFiGroupMapping) {
                        if ('deleteForm' === $formName) {
                            $mappingManager->delete($mapping);
                            $this->addFlash('success', _('Mapping deleted.'));
                        } else {
                            $direction = self::stringOrNull($actionForm->get('direction')->getData());
                            if (null !== $direction) {
                                $mappingManager->move($mapping, $direction);
                            }
                        }
                    }

                    return $this->redirectToRoute('unificonnector_configuration');
                }
            }
        }

        $content = $this->renderView('configuration/index.html.twig', [
            'connectionForm' => $connectionForm->createView(),
            'mappingForm' => $mappingForm->createView(),
            'mappings' => array_map(static fn(array $item): array => [
                'mapping' => $item['mapping'],
                'moveUpForm' => $item['moveUpForm']->createView(),
                'moveDownForm' => $item['moveDownForm']->createView(),
                'deleteForm' => $item['deleteForm']->createView(),
            ], $mappingViews),
        ]);
        $response = $this->createResponseBuilder($content)
            ->setTitle(_('UniFi Connector'))
            ->addBreadcrumb($breadcrumbs->root())
            ->addBreadcrumb(_('Network'))
            ->addBreadcrumb(_('UniFi Connector'))
            ->addStylesheet($packages->getUrl('js/iserv.css', 'iserv-js'))
            ->addStylesheet($packages->getUrl('css/bootstrap.css', 'iserv-bootstrap'))
            ->addStylesheet($packages->getUrl('js/form.css', 'iserv-form'))
            ->addStylesheet($packages->getUrl('js/autocomplete.css', 'iserv-autocomplete'))
            ->addScript($packages->getUrl('js/jquery.js', 'iserv-js'))
            ->addScript($packages->getUrl('js/bootstrap.js', 'iserv-js'))
            ->addScript($packages->getUrl('js/iserv.js', 'iserv-js'))
            ->addScript($packages->getUrl('js/bootstrap.js', 'iserv-bootstrap'))
            ->addScript($packages->getUrl('js/form.js', 'iserv-form'))
            ->addScript($packages->getUrl('js/autocomplete.js', 'iserv-autocomplete'));
        $translationAssets->loadIntoBuilder($response, ['iserv-js', 'iserv-bootstrap', 'iserv-form', 'iserv-autocomplete']);

        return $response->getResponseContent();
    }

    private function createConnectionForm(FileConfigurationRepository $repository, Request $request): FormInterface
    {
        $settings = new ConnectionSettings();
        if (null !== $existing = $repository->find()) {
            $settings->url = $existing->url;
            $settings->username = $existing->username;
            $settings->password = $existing->password;
            $settings->fallbackGroup = $existing->fallbackGroup;
        }
        $form = $this->createForm(ConnectionSettingsType::class, $settings);
        $form->handleRequest($request);

        return $form;
    }

    private function createActionForm(FormFactoryInterface $forms, string $name, string $id, string $label, string $icon, ?string $direction, Request $request): FormInterface
    {
        $form = $forms->createNamed($name, MappingActionType::class, ['id' => $id], ['label' => $label, 'icon' => $icon, 'direction' => $direction]);
        $form->handleRequest($request);

        return $form;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

}
