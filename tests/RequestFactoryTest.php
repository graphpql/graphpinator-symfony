<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony\Tests;

use Graphpinator\Request\Exception\InvalidMethod;
use Graphpinator\Request\Exception\InvalidMultipartRequest;
use Graphpinator\Request\Exception\QueryMissing;
use Graphpinator\Request\Exception\UnknownKey;
use Graphpinator\Symfony\RequestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

final class RequestFactoryTest extends TestCase
{
    public function testGetRequestWithQueryParameter() : void
    {
        $httpRequest = HttpRequest::create(
            '/?query={hello}',
            'GET',
        );

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertEquals(new \stdClass(), $request->variables);
        self::assertNull($request->operationName);
    }

    public function testGetRequestWithQueryAndVariables() : void
    {
        $httpRequest = HttpRequest::create(
            '/?query={hello(name:$name)}&variables={"name":"World"}',
            'GET',
        );

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello(name:$name)}', $request->query);
        self::assertIsObject($request->variables);
        self::assertSame('World', $request->variables->name);
        self::assertNull($request->operationName);
    }

    public function testGetRequestWithOperationName() : void
    {
        $httpRequest = HttpRequest::create(
            '/?query={hello}&operationName=GetHello',
            'GET',
        );

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertEquals(new \stdClass(), $request->variables);
        self::assertSame('GetHello', $request->operationName);
    }

    public function testPostRequestWithJsonContentType() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"query":"{hello}","variables":{"name":"World"},"operationName":"GetHello"}',
        );

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertIsObject($request->variables);
        self::assertSame('World', $request->variables->name);
        self::assertSame('GetHello', $request->operationName);
    }

    public function testPostRequestWithGraphQLContentType() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/graphql'],
            '{hello}',
        );

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertEquals(new \stdClass(), $request->variables);
        self::assertNull($request->operationName);
    }

    public function testPostRequestWithMultipartFormData() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundary'],
        );
        $httpRequest->request->set('operations', '{"query":"{hello}"}');

        $factory = new RequestFactory($httpRequest);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertEquals(new \stdClass(), $request->variables);
        self::assertNull($request->operationName);
    }

    public function testPostRequestWithMultipartFormDataWithoutOperations() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundary'],
        );

        $factory = new RequestFactory($httpRequest);

        $this->expectException(InvalidMultipartRequest::class);
        $factory->create();
    }

    public function testInvalidMethod() : void
    {
        $httpRequest = HttpRequest::create('/', 'PUT');
        $factory = new RequestFactory($httpRequest);

        $this->expectException(InvalidMethod::class);
        $factory->create();
    }

    public function testStrictModeEnabled() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"query":"{hello}","extraKey":"value"}',
        );

        $factory = new RequestFactory($httpRequest, strict: true);

        $this->expectException(UnknownKey::class);
        $factory->create();
    }

    public function testStrictModeDisabled() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"query":"{hello}","extraKey":"value"}',
        );

        $factory = new RequestFactory($httpRequest, strict: false);
        $request = $factory->create();

        self::assertSame('{hello}', $request->query);
        self::assertEquals(new \stdClass(), $request->variables);
        self::assertNull($request->operationName);
    }

    public function testEmptyJsonContent() : void
    {
        $httpRequest = HttpRequest::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}',
        );

        $factory = new RequestFactory($httpRequest);

        $this->expectException(QueryMissing::class);
        $factory->create();
    }

    public function testGetRequestWithoutQuery() : void
    {
        $httpRequest = HttpRequest::create('/', 'GET');
        $factory = new RequestFactory($httpRequest);

        $this->expectException(QueryMissing::class);
        $factory->create();
    }
}
