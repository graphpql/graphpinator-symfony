<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests;

use Graphpinator\Symfony\GraphQLController;
use Graphpinator\Typesystem\Schema;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GraphQLControllerTest extends TestCase
{
    public function testOptionsReturnsResponseWithCorsHeaders() : void
    {
        $schema = $this->createMock(Schema::class);
        $logger = $this->createMock(LoggerInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $controller = new GraphQLController($schema, $logger, $cache, $urlGenerator);
        $response = $controller->options();

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('true', $response->headers->get('Access-Control-Allow-Credentials'));
        self::assertSame('HEAD, GET, POST, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
        self::assertSame('*', $response->headers->get('Access-Control-Allow-Headers'));
        self::assertSame('86400', $response->headers->get('Access-Control-Max-Age'));
    }

    public function testApplyCorsHeadersAddsCorrectHeaders() : void
    {
        $schema = $this->createMock(Schema::class);
        $logger = $this->createMock(LoggerInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $controller = new class ($schema, $logger, $cache, $urlGenerator) extends GraphQLController {
            public function testApplyCorsHeaders(Response $response) : Response
            {
                return $this->applyCorsHeaders($response);
            }
        };

        $response = new Response();
        $responseWithCors = $controller->testApplyCorsHeaders($response);

        self::assertSame('*', $responseWithCors->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('true', $responseWithCors->headers->get('Access-Control-Allow-Credentials'));
        self::assertSame('HEAD, GET, POST, OPTIONS', $responseWithCors->headers->get('Access-Control-Allow-Methods'));
        self::assertSame('*', $responseWithCors->headers->get('Access-Control-Allow-Headers'));
        self::assertSame('86400', $responseWithCors->headers->get('Access-Control-Max-Age'));
    }
}
