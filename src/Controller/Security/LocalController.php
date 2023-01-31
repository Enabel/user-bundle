<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LocalController extends AbstractController
{
    #[Route(path: '/auth/login', name: 'enabel_login')]
    public function login(AuthenticationUtils $authenticationUtils, ParameterBagInterface $parameterBag): Response
    {
        if (null !== $this->getUser()) {
            /** @var string $defaultTarget */
            $defaultTarget = $parameterBag->get('enabel_user.login_redirect_route');
            return $this->redirectToRoute($defaultTarget);
        }

        return $this->render(
            '@EnabelUser/auth/login.html.twig',
            [
                // last username entered by the user (if any)
                'last_username' => $authenticationUtils->getLastUsername(),
                // last authentication error (if any)
                'error' => $authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * @return never
     */
    #[Route(path: '/auth/logout', name: 'enabel_logout')]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - Intercepted by the logout key on your firewall.');
    }
}
