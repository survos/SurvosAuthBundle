<?php

// in bundle
/*
survos_auth:
resource: '@SurvosAuthBundle/config/routes.php'
    prefix: '/auth'
*/
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Survos\AuthBundle\Controller\OAuthController;
return function (RoutingConfigurator $routes) {
    $routes->add('survos_auth', '/auth')
        ->controller([OAuthController::class, 'auth'])
    ;

    $routes->add('oauth_providers', '/oauth_providers')
        ->controller([OAuthController::class, 'providers'])
    ;
    $routes->add('oauth_provider', '/oauth_provider/{providerKey}')
        ->controller([OAuthController::class, 'providerDetail'])
    ;
    // where the user is redirected to AFTER the provider has interacted with them.
    $routes->add('oauth_connect_check', '/connect/controller/{clientKey}')
        ->controller([OAuthController::class, 'connectCheckWithController'])
    ;
    // this redirects the user to the provider
    $routes->add('oauth_connect_start', '/social_login/{clientKey}')
        ->controller([OAuthController::class, 'connectAction'])
    ;



};

