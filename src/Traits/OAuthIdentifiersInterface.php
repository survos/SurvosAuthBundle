<?php

namespace Survos\AuthBundle\Traits;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

interface OAuthIdentifiersInterface
{

    const GITHUB = 'github';

    public function getIdentifiers(): ?array;
    public function getIdentifierData(string $clientKey): ?array;
    public function setIdentifiers(?array $identifiers): self;
    public function setIdentifier(string $clientKey, string $token): self;

}
