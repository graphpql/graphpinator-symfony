<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Graphpinator\Symfony\DependencyInjection\GraphpinatorExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class GraphpinatorBundle extends AbstractBundle
{
    #[\Override]
    public function getPath() : string
    {
        return \dirname(__DIR__);
    }

    #[\Override]
    public function getContainerExtension() : ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new GraphpinatorExtension();
        }

        return $this->extension;
    }
}
