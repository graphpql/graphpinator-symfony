<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests;

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
}
