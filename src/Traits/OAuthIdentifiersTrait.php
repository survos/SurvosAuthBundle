<?php

namespace Survos\AuthBundle\Traits;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

trait OAuthIdentifiersTrait
{

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['oauth.read'])]
    private ?array $identifiers=null;

    public function getIdentifiers(): ?array
    {
        return $this->identifiers;
    }

    public function getIdentifierData(string $clientKey): ?array
    {
        return $this->getIdentifiers()[$clientKey]??null;

    }

    public function setIdentifiers(?array $identifiers): self
    {
        $this->identifiers = $identifiers;
        return $this;
    }
    public function setIdentifier(string $clientKey, string|array $token): self
    {
        $this->identifiers[$clientKey] = $token;
        return $this;
    }

}
