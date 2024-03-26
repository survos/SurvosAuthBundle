<?php

namespace Survos\AuthBundle\Twig\Components;

use Survos\AuthBundle\Services\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('OAuth', '@SurvosAuth/components/OAuth.html.twig')]
final class OAuth
{
    public string $buttonClass='button';

    // <i class="bi bi-github"></i>
    public string $iconPrefix='bi bi-'; // hack

    public function __construct(private AuthService $authService)
    {
//        $this->clientKeys = $this->authService->getOauthClientKeys();
    }

    public function getClientKeys(): array
    {
        return  $this->authService->getOauthClientKeys();
    }
}
