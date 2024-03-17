<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Graphpinator\ErrorHandlingMode;
use Graphpinator\Graphpinator;
use Graphpinator\Module\ModuleSet;
use Graphpinator\PersistedQueries\PersistedQueriesModule;
use Graphpinator\Printer\HtmlVisitor;
use Graphpinator\Printer\Printer;
use Graphpinator\Printer\TextVisitor;
use Graphpinator\Printer\TypeKindSorter;
use Graphpinator\Symfony\FileProvider;
use Graphpinator\Symfony\RequestFactory;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Upload\UploadModule;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GraphQLController extends AbstractController
{
    public function __construct(
        private Schema $schema,
        private LoggerInterface $logger,
        private CacheItemPoolInterface $cache,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    #[Route(name: 'options', methods: ['OPTIONS'])]
    public function options() : Response
    {
        $response = new Response();
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'HEAD, GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }

    #[Route(name: 'graphql', methods: ['GET', 'POST'])]
    public function graphql(Request $request) : JsonResponse
    {
        $errorHandling = $request->server->get('app.debug') === true
            ? ErrorHandlingMode::OUTPUTABLE
            : ErrorHandlingMode::ALL;
        $graphpinator = new Graphpinator($this->schema, $errorHandling, $this->getEnabledModules($request), $this->logger);
        $requestFactory = new RequestFactory($request);

        return new JsonResponse($graphpinator->run($requestFactory));
    }

    #[Route('/ui', name: 'ui', methods: ['GET'])]
    public function ui() : Response
    {
        return $this->render('@Graphpinator/ui.html.twig', [
            'schemaUrl' => $this->urlGenerator->generate('graphql_default_graphql', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    #[Route('/schema', name: 'schema', methods: ['GET'])]
    public function schema() : Response
    {
        $printer = new Printer(new HtmlVisitor(), new TypeKindSorter());
        $schemaHtml = $printer->printSchema($this->schema);

        return $this->render('@Graphpinator/schema.html.twig', [
            'schema' => $schemaHtml,
        ]);
    }

    #[Route('/schema.graphql', name: 'schema-file', methods: ['GET'])]
    public function schemaFile() : Response
    {
        $printer = new Printer(new TextVisitor(), new TypeKindSorter());
        $schemaText = $printer->printSchema($this->schema);

        $response = new Response($schemaText);
        $response->headers->set('Content-Type', 'application/graphql');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'schema.graphql'),
        );

        return $response;
    }

    protected function getEnabledModules(Request $request) : ModuleSet
    {
        return new ModuleSet([
            new UploadModule(new FileProvider($request)),
            new PersistedQueriesModule($this->schema, new Psr16Cache($this->cache)),
            //new MaxDepthModule(),
            //new MaxNodesModule(),
        ]);
    }
}
