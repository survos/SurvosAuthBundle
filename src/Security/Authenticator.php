<?php

namespace Survos\AuthBundle\Security;

use App\Entity\User;

// your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Survos\AuthBundle\Traits\OAuthIdentifiersInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Custom class to simplify loading the user from an oAuth provider
 *
 */
class Authenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    public function __construct(private ClientRegistry         $clientRegistry,
                                private EntityManagerInterface $entityManager,
                                private RouterInterface        $router,
                                private string                 $userClass,
                                private string                 $newUserRedirectRoute,
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'oauth_connect_check';
    }

    public function authenticate(Request $request): Passport
    {
        $clientKey = $request->get('clientKey');
//        dd($request->query->all());
        $client = $this->clientRegistry->getClient($clientKey);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $clientKey) {
                /** @var OAuth2ClientInterface $facebookUser */
                $oAuthUser = $client->fetchUserFromToken($accessToken);

                $identifier = $oAuthUser->getId();
                $email = method_exists($oAuthUser, 'getEmail')
                    ? $oAuthUser->getEmail()
                    : $oAuthUser->toArray()['email'] ?? null;

                if (empty($email)) {
                    dd($oAuthUser);
                }
                assert($email, "missing email");
                // 1) have they logged in before?
                $existingUser = $this->entityManager->getRepository($this->userClass)->findOneBy(['email' => $email]);

                // create a user with an empty password and the oauth info.  But then we need to redirect to /register
                /** @var OAuthIdentifiersInterface $user */
                $user = null;
                if (!$existingUser) {
                    // should be a setting in the bundle if this is the desired behavior
                    $user = (new $this->userClass)->setEmail($email);
                } else {
                    $user = $existingUser;
                }
                // now update the provider keys.
                if ($user) {
                    $user->setIdentifier($clientKey, [
                        'accessToken' => json_decode(json_encode($accessToken)),
                        'token' => $identifier, 'data' => $oAuthUser->toArray()]);
//                dd($accessToken, $email, $oAuthUser->toArray(), $identifier, $user, $user->getIdentifiers());

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $clientKey = $request->attributes->get('clientKey');
        // @todo: only if new user, otherwise let it continue normally.
//        dd($token, $firewallName, $request);
        // if we wanted to hide the userid, we could set it in a session
        $targetUrl = $this->router->generate($this->newUserRedirectRoute, [
            'userId' => $token->getUser()->getUserIdentifier(),
            'clientKey' => $clientKey
        ]);

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
//        dd($request, $exception, $message);

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
