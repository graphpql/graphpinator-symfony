<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests;

use Graphpinator\Symfony\DependencyInjection\GraphpinatorExtension;
use Graphpinator\Symfony\GraphpinatorBundle;
use PHPUnit\Framework\TestCase;

final class GraphpinatorBundleTest extends TestCase
{
    public function testGetPathReturnsCorrectDirectory() : void
    {
        $bundle = new GraphpinatorBundle();
        $path = $bundle->getPath();

        self::assertStringEndsWith('graphpinator-symfony', $path);
        self::assertDirectoryExists($path);
    }

    public function testGetContainerExtensionReturnsGraphpinatorExtension() : void
    {
        $bundle = new GraphpinatorBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(GraphpinatorExtension::class, $extension);
    }

    public function testGetContainerExtensionReturnsSameInstance() : void
    {
        $bundle = new GraphpinatorBundle();
        $extension1 = $bundle->getContainerExtension();
        $extension2 = $bundle->getContainerExtension();

        self::assertSame($extension1, $extension2);
    }
}
