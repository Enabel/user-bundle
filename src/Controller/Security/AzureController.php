<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class AzureController extends AbstractController
{
    #[Route(path: '/azure/login', name: 'enabel_azure_login')]
    public function azureLogin(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('azure_o365')
            ->redirect(
                [
                    'openid',
                    'profile',
                    'user.read',
                ],
                []
            );
    }

    /** @codeCoverageIgnore */
    #[Route(path: '/azure/check', name: 'enabel_azure_check')]
    public function azureCheck(): void
    {
        throw new \LogicException('This method can be blank - Intercepted by the oauth2 client');
    }
}
