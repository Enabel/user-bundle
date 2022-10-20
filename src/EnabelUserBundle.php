<?php

declare(strict_types=1);

namespace Enabel\UserBundle;

use Enabel\UserBundle\DependencyInjection\UserBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EnabelUserBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new UserBundleExtension();
    }
}
