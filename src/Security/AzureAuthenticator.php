<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Security;

use Enabel\UserBundle\Entity\User;
use Enabel\UserBundle\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

class AzureAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'enabel_azure_login';
    public const LOGIN_CHECK = 'enabel_azure_check';

    public function __construct(
        private ClientRegistry $clientRegistry,
        private UrlGeneratorInterface $urlGenerator,
        private ParameterBagInterface $parameterBag,
        private UserRepository $userRepository
    ) {
    }

    final public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === self::LOGIN_CHECK;
    }

    /** @codeCoverageIgnore */
    final public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('azure_o365');
        $accessToken = $this->fetchAccessToken($client);
        /** @var AzureResourceOwner $azureUser */
        $azureUser = $client->fetchUserFromToken($accessToken);

        if (null !== $azureUser) {
            $userIdentifier = $azureUser->getUpn();
            if (null !== $userIdentifier) {
                return new SelfValidatingPassport(
                    new UserBadge($userIdentifier, function () use ($azureUser) {
                        return $this->userRepository->findOrCreateFromAzure($azureUser);
                    })
                );
            }
        }

        throw new AuthenticationException('Authentication failed', 401);
    }

    /** @codeCoverageIgnore */
    final public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $user = $token->getUser();

        if ($user instanceof User) {
            $this->userRepository->setLastLogin($user);
        }

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        /** @var string $route */
        $route = $this->parameterBag->get('enabel_user.login_redirect_route');
        return new RedirectResponse($this->urlGenerator->generate($route));
    }

    /** @codeCoverageIgnore */
    final public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /** @codeCoverageIgnore */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
