<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Graphpinator\Request\Exception\InvalidMethod;
use Graphpinator\Request\Exception\InvalidMultipartRequest;
use Graphpinator\Request\JsonRequestFactory;
use Graphpinator\Request\Request;
use Graphpinator\Request\RequestFactory as RequestFactoryInterface;
use Infinityloop\Utils\Json;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

final class RequestFactory implements RequestFactoryInterface
{
    public function __construct(
        private HttpRequest $request,
        private bool $strict = true,
    )
    {
    }

    #[\Override]
    public function create() : Request
    {
        $method = $this->request->getMethod();

        if (!\in_array($method, ['GET', 'POST'], true)) {
            throw new InvalidMethod();
        }

        $contentType = $this->request->headers->get('Content-Type');

        if (\is_string($contentType) && \str_starts_with($contentType, 'multipart/form-data')) {
            if ($method === 'POST' && $this->request->getPayload()->has('operations')) {
                return $this->applyJsonFactory(Json::fromString($this->request->getPayload()->get('operations')));
            }

            throw new InvalidMultipartRequest();
        }

        switch ($contentType) {
            case 'application/graphql':
                return new Request($this->request->getContent());
            case 'application/json':
                return $this->applyJsonFactory(Json::fromString($this->request->getContent()));
            default:
                $params = $this->request->query->all();

                if (\array_key_exists('variables', $params)) {
                    $params['variables'] = Json::fromString($params['variables'])->toNative();
                }

                return $this->applyJsonFactory(Json::fromNative((object) $params));
        }
    }

    private function applyJsonFactory(Json $json) : Request
    {
        return (new JsonRequestFactory($json, $this->strict))->create();
    }
}
