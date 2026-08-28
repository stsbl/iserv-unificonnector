<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Controller;

use IServ\UnifiConnector\Infrastructure\Form\SyncActionType;
use IServ\UnifiConnector\Security\AdminAuthenticatedVoter;
use IServ\UnifiConnector\Synchronisation\SyncRunner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/unificonnector/sync')]
final class SyncController extends AbstractController
{
    #[Route('', name: 'unificonnector_sync', methods: ['POST'])]
    public function sync(Request $request, FormFactoryInterface $forms, SyncRunner $runner): StreamedResponse
    {
        $this->denyAccessUnlessGranted(AdminAuthenticatedVoter::ATTR_IS_ADMIN);
        $form = $forms->createNamed('unificonnector_sync', SyncActionType::class, options: [
            'action' => $this->generateUrl('unificonnector_sync'),
        ]);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createAccessDeniedException();
        }

        $response = new StreamedResponse(static function () use ($runner): void {
            $runner->stream(static function (string $output): void {
                echo $output;
                flush();
            });
        });
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
