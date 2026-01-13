<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigNamespaceTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp() : void
    {
        $this->kernel = new class('test', true) extends Kernel {
            public function registerBundles() : array
            {
                return [
                    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new \Symfony\Bundle\TwigBundle\TwigBundle(),
                    new \Graphpinator\Symfony\GraphpinatorBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader) : void
            {
                $loader->load(function (ContainerBuilder $container) : void {
                    $container->loadFromExtension('framework', [
                        'secret' => 'test',
                        'test' => true,
                    ]);
                    
                    $container->loadFromExtension('twig', [
                        'default_path' => '%kernel.project_dir%/templates',
                    ]);
                });
            }
            
            public function getCacheDir() : string
            {
                return \sys_get_temp_dir() . '/graphpinator_test_cache';
            }
            
            public function getLogDir() : string
            {
                return \sys_get_temp_dir() . '/graphpinator_test_logs';
            }
        };
        
        $this->kernel->boot();
    }

    protected function tearDown() : void
    {
        $cacheDir = $this->kernel->getCacheDir();
        $this->kernel->shutdown();
        
        // Clean up cache
        if (\is_dir($cacheDir)) {
            $this->removeDirectory($cacheDir);
        }
    }

    public function testGraphpinatorNamespaceIsRegistered() : void
    {
        $container = $this->kernel->getContainer();
        
        /** @var Environment $twig */
        $twig = $container->get('twig');
        $loader = $twig->getLoader();
        
        self::assertInstanceOf(FilesystemLoader::class, $loader);
        
        // Verify namespace exists
        $namespaces = $loader->getNamespaces();
        self::assertContains('Graphpinator', $namespaces, 'Graphpinator namespace should be registered');
        
        // Verify templates exist and can be loaded
        self::assertTrue(
            $loader->exists('@Graphpinator/ui.html.twig'),
            'Template @Graphpinator/ui.html.twig should exist',
        );
        
        self::assertTrue(
            $loader->exists('@Graphpinator/schema.html.twig'),
            'Template @Graphpinator/schema.html.twig should exist',
        );
    }

    public function testTemplatesCanBeRendered() : void
    {
        $container = $this->kernel->getContainer();
        
        /** @var Environment $twig */
        $twig = $container->get('twig');
        
        // Test ui.html.twig renders without errors
        $uiOutput = $twig->render('@Graphpinator/ui.html.twig', [
            'schemaUrl' => 'http://example.com/graphql',
        ]);
        
        self::assertStringContainsString('GraphiQL', $uiOutput);
        self::assertStringContainsString('http://example.com/graphql', $uiOutput);
        
        // Test schema.html.twig renders without errors
        $schemaOutput = $twig->render('@Graphpinator/schema.html.twig', [
            'schema' => '<div>Test Schema</div>',
        ]);
        
        self::assertStringContainsString('Schema.graphql', $schemaOutput);
        self::assertStringContainsString('Test Schema', $schemaOutput);
    }
    
    private function removeDirectory(string $dir) : void
    {
        if (!\is_dir($dir)) {
            return;
        }
        
        $files = \array_diff(\scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            \is_dir($path) ? $this->removeDirectory($path) : \unlink($path);
        }
        
        \rmdir($dir);
    }
}
