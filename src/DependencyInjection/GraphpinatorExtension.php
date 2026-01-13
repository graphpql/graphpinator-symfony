<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

final class GraphpinatorExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Load services if needed in future
    }

    public function prepend(ContainerBuilder $container): void
    {
        // Register Twig namespace for bundle templates
        $container->prependExtensionConfig('twig', [
            'paths' => [
                \dirname(__DIR__) . '/Resources/views' => 'Graphpinator',
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'graphpinator';
    }
}
