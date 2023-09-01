<?php

namespace Survos\AuthBundle\Traits;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

trait OAuthIdentifiersTrait
{

    #[ORM\Column(Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['instance.read'])]
    private ?array $identifiers=null;

    public function getIdentifiers(): ?array
    {
        return $this->identifiers;
    }

    public function setIdentifiers(?array $identifiers): self
    {
        $this->identifiers = $identifiers;
        return $this;
    }
    public function setIdentifier(string $clientKey, string $token): self
    {
        $this->identifiers[$clientKey] = $token;
        return $this;
    }

}
