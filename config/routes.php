<?php

// in bundle
/*
survos_auth:
resource: '@SurvosAuthBundle/config/routes.php'
    prefix: '/auth'
*/
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('survos_auth', '/auth')
        ->controller('survos.auth.oauth_controller')
    ;
    $routes->add('oauth_providers', '/oauth_providers')
        ->controller('survos.auth.oauth_controller::providers')

    ;
};
