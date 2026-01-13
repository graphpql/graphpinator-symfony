<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests;

use Graphpinator\Symfony\DependencyInjection\GraphpinatorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GraphpinatorExtensionTest extends TestCase
{
    public function testGetAliasReturnsGraphpinator() : void
    {
        $extension = new GraphpinatorExtension();

        self::assertSame('graphpinator', $extension->getAlias());
    }

    public function testPrependRegistersTwigPaths() : void
    {
        $extension = new GraphpinatorExtension();
        $container = new ContainerBuilder();

        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');
        self::assertNotEmpty($twigConfig);
        self::assertArrayHasKey('paths', $twigConfig[0]);

        $paths = $twigConfig[0]['paths'];
        self::assertIsArray($paths);
        self::assertNotEmpty($paths);

        // Verify that the Graphpinator namespace is registered
        $graphpinatorPath = null;

        foreach ($paths as $path => $namespace) {
            if ($namespace === 'Graphpinator') {
                $graphpinatorPath = $path;

                break;
            }
        }

        self::assertNotNull($graphpinatorPath);
        self::assertStringEndsWith('/Resources/views', $graphpinatorPath);
        self::assertDirectoryExists($graphpinatorPath);
    }

    public function testLoadDoesNotThrowException() : void
    {
        $extension = new GraphpinatorExtension();
        $container = new ContainerBuilder();

        // Should not throw any exception
        $extension->load([], $container);

        self::assertTrue(true);
    }
}
