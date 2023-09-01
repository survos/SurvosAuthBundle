<?php

namespace Survos\AuthBundle\Controller;

# use App\Security\AppAuthenticator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Exception\IdentityProviderAuthenticationException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Survos\AuthBundle\Services\AuthService;
use Survos\AuthBundle\Traits\OAuthIdentifiersInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManagerInterface;
use Symfony\Component\Security\Http\Authentication\Provider\AuthenticationProviderInterface;
use Twig\Environment;

class OAuthController extends AbstractController
{
//    private ?UserProviderInterface  $userProvider = null;

    public function __construct(
        private AuthService $baseService,
        private Registry $registry,
        private RouterInterface $router,
        private ClientRegistry $clientRegistry,
        private UserProviderInterface $userProvider,
        private EntityManagerInterface $entityManager,
        private string $userClass,
    ) {
        $this->entityManager = $this->registry->getManagerForClass($this->userClass);
        //        dd($this->clientRegistry);
        //        $this->clientRegistry = $this->baseService->getClientRegistry();
    }

//    public function setUserProvider(UserProviderInterface $userProvider)
//    {
//        $this->userProvider = $userProvider;
//    }

    public function socialMediaButtons($style = '')
    {
        return $this->render('@SurvosAuth/_social_media_login_buttons.html.twig', [
            'clientKeys' => $this->clientRegistry->getEnabledClientKeys(),
            'clientRegistry' => $this->clientRegistry,
            'style' => $style,
        ]);
    }

    #[Route("/provider/{providerKey}", name: "oauth_provider")]
    public function providerDetail(Request $request, $providerKey)
    {
        $bundles = $this->getParameter('kernel.bundles');
        $provider = $this->baseService->getCombinedOauthData()[$providerKey];

        // look in composer.lock for the library
        $composer = $this->getParameter('kernel.project_dir') . '/composer.lock';
        if (! file_exists($composer)) {
        }

        $packages = json_decode(file_get_contents($composer))->packages;
        $package = array_filter($packages, function ($package) use ($provider) {
            return $provider['library'] === $package->name;
        });

        // throw new \Exception($provider['class'], class_exists($provider['class']));

        return $this->render('@SurvosAuth/oauth/provider.html.twig', [
            'provider' => $provider,
            'package' => $package,
            'classExists' => class_exists($provider['class']),
        ]);
    }

    #[Route("/providers", name: "oauth_providers")]
    public function providers(Request $request)
    {
        $oauthClients = $this->baseService->getOauthClients();
        $clientRegistry = $this->clientRegistry;

        $refresh = $request->get('refresh', false);

        // what we want is ALL the available clients, with their configuration if available.

        // could move the array_map into the service call
        $clients = $this->baseService->getCombinedOauthData();

        return $this->render('@SurvosAuth/oauth/providers.html.twig', [
            'clients' => $clients,
            /*
            'clientKeys' =>  $clientRegistry->getEnabledClientKeys(),
            'clientRegistry' => $clientRegistry
            */
        ]);
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/social_login/{clientKey}", name="oauth_connect_start")
     */
    public function connectAction(string $clientKey)
    {
        // scopes are client-specific, need to put them in survos_oauth or base or (ideally) in knp's config
        $scopes =
            [
                'github' => [
                    "user:email", "read:user",
                ],
                'facebook' => ['email', 'public_profile'],
                'google' => ['email', 'profile', 'openid'],
            ];
        ;
        // will redirect to an external OAuth server
        $redirect = $this->clientRegistry
            ->getClient($clientKey) // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect($scopes[$clientKey] ?? [], []);
        //        dump($redirect->getTargetUrl());
        $redirect->setTargetUrl(str_replace('http%3A', 'https%3A', $redirect->getTargetUrl()));
        //         throw new \Exception($redirect);
        return $redirect;
    }

    /**
     * This is where the user is redirected to after logging into the OAuth server,
     * see the "redirect_route" in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/controller/{clientKey}", name="oauth_connect_check")
     */
    public function connectCheckWithController(
        Request $request,
        string $clientKey
    ) {
        $route = $request->get('_route');
        $clientRegistry = $this->clientRegistry;

        /** @var OAuth2ClientInterface $client */
        $client = $clientRegistry->getClient($clientKey);

        // the exact class depends on which provider you're using
        /** @  var \League\OAuth2\Client\Provider\GenericProvider $user */
        $oAuthUser = $client->fetchUser();
        //            $email = $oAuthUser->getEmail();
        $identifier = $oAuthUser->getId();
        // now presumably we need to link this up.
        $token = $oAuthUser->getId();

        $email = method_exists($oAuthUser, 'getEmail') ? $oAuthUser->getEmail() : null;
        assert($email);

        try {
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            foreach ($request->query->all() as $var => $value) {
                $this->addFlash('warning', sprintf("%s: %s", $var, $value));
            }
            return $this->redirectToRoute('app_login');
        }

        // do something with all this new power!
        // e.g. $name = $user->getFirstName();
//            throw new \Exception($oAuthUser); die;
        // ...

        try {
        } catch (IdentityProviderAuthenticationException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            $this->addFlash('error', $e->getMessage());
        }

        if ($error = $request->get('error')) {
            $this->addFlash('error', $error);
            $this->addFlash('error', $request->get('error_description'));
            return $this->redirectToRoute('app_login');
        }

        // do something with all this new power!
        // e.g. $name = $user->getFirstName();

        // if we have it, just log them in.  If not, direct to register

        // it seems that loadUserByUsername redirects to login
        try {
            /** @var UserInterface&OAuthIdentifiersInterface $user */
            $user = $this->userProvider->loadUserByIdentifier($email);
        } catch (UserNotFoundException $exception) {
            return new RedirectResponse($this->generateUrl('app_register', [
                'email' => $email,
                'id' => $identifier,
                'client' => $clientKey,
            ]));
        }

//        if ($user = $em->getRepository(User::class)->findOneBy(['email' => $email])) {
// after validating the user and saving them to the database
        // authenticate the user and use onAuthenticationSuccess on the authenticator
        // if it's already in there, update the token.  This also happens with registration, so maybe belongs in AuthService?
        if ($user->getUserIdentifier()) {
            $user->setIdentifier($clientKey, $token);
            //                $passport = $authentication->auth
            $this->entityManager->flush();
            // boo, we need a better redirect!
            $successRedirect = $this->redirectToRoute('app_homepage', [
                'email' => $email,
            ]);

            return $successRedirect;
        }

//            // ...
//        } catch (IdentityProviderException $e) {
//            // something went wrong!
//            // probably you should return the reason to the user
//            echo $e->getResponseBody();
//            throw new \Exception($e, $e->getMessage());
//        }

        return new RedirectResponse($this->generateUrl('app_register', [
            'email' => $email,
            'clientKey' => $clientKey,
            'token' => $token,
        ]));
    }

    /**
     * After going to Github, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/check-guard", name="connect_check_with_guard")
     * @deprecated
     */
    private function connectCheckAction(Request $request, UserProviderInterface $userProvider)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        // leave it blank, per the instructions, and handle the redirect in the Guard

        // if it comes back from the guard to here,
        $user = $this->getUser();
        if ($user->getId()) {
            $targetUrl = $this->router->generate('app_homepage', [
                'login' => 'success',
            ]);
        } else {
            $targetUrl = $this->router->generate('app_register', [
                'email' => $user->getEmail(),
            ]);
        }
        return new RedirectResponse($targetUrl);
    }
}
