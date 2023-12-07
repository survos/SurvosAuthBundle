<?php

namespace Survos\AuthBundle\Twig\Components;

use Survos\AuthBundle\Services\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('OAuth', '@SurvosAuth/components/OAuth.html.twig')]
final class OAuth
{
    public function __construct(private AuthService $authService)
    {
//        $this->clientKeys = $this->authService->getOauthClientKeys();
    }

    public function getClientKeys(): array
    {
        return  $this->authService->getOauthClientKeys();
    }
}
