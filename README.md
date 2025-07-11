# Graphpinator Symfony 

:zap::globe_with_meridians::zap: Graphpinator adapters and addons for Symfony framework.

## Introduction

This package includes adapters and tools to easily integrate Graphpinator into a Symfony application.

## Installation

Install package using composer

```composer require graphpql/graphpinator-symfony```

## How to use

### Register a bundle

Add a bundle entry to the `bundles.php`. Currently the bundle is only used for an access to the twig namespace, so this step can be ommited when the rendering actions will not be used.

```php
Graphpinator\Symfony\GraphpinatorBundle::class => ['all' => true],
```

### Configure dependency injection

At first we need to configure Symfony to find all our types and tag them, so we can inject them into our type registry.

```yaml
services:
    # Find and register all types into the DI container
    App\GraphQL\Default\Types\:
        resource: '../src/GraphQL/Default/Types'
        public: true # not needed when you do not have any accessors (see the cyclic dependencies section of this documentation)
        tags:
            - 'graphql.default.types'
    # Find and register all directives into the DI container
    App\GraphQL\Default\Directives\:
        resource: '../src/GraphQL/Default/Directives'
        tags:
            - 'graphql.default.directives'

    # Any additional types must be also registred and tagged to become available in the type container
    Graphpinator\ExtraTypes\EmailAddressType:
        tags:
            - 'graphql.default.types'
    Graphpinator\ExtraTypes\UrlType:
        tags:
            - 'graphql.default.types'
```

Create a specific `Container` service for each schema and instruct the DI to inject it with the types and directives using the tags we configured.

```php
<?php declare(strict_types = 1);

namespace App\GraphQL\Default;

use Graphpinator\SimpleContainer;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class Container extends SimpleContainer
{
    public function __construct(
        #[TaggedIterator('graphql.default.types')]
        iterable $types,
        #[TaggedIterator('graphql.default.directives')]
        iterable $directives,
    )
    {
        parent::__construct([...$types], [...$directives]);
    }
}
```

Create a `Schema` service.

```php
<?php declare(strict_types = 1);

namespace App\GraphQL\Default;

use App\GraphQL\Default\Container;
use Graphpinator\Typesystem\Schema as BaseSchema;

final class Schema extends BaseSchema
{
    public function __construct(Container $container)
    {
        parent::__construct($container, $container->getType('Query'), $container->getType('Mutation'));

        // You may also configure the schema there
        $this->setDescription('My GraphQL API');
    }
}
```

Now the `Schema` is set up and can be used to execute requests on it.

#### Cyclic dependendencies

When using abstract types, the cyclic dependencies must be avoided using accessors. In Symfony we need to create a simple service to extract the types from a container.

```php
final class CandidateAccessor
{
    public function __construct(private \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
    }

    public function slideSingle() : SlideSingle
    {
        return $this->container->get(SlideSingle::class);
    }

    public function slideDouble() : SlideDouble
    {
        return $this->container->get(SlideDouble::class);
    }

    public function slideTriple() : SlideTriple
    {
        return $this->container->get(SlideTriple::class);
    }
}

```

Configure the accessor service to recieve the DI container as an argument.

```yaml
services:
    SlideAccessor:
        arguments:
            $container: '@service_container'
```

This service is than injected into the abstract type instead of the concrete types in order to break the dependency cycle.

#### Multiple schemas

Some more sophisticated applications may require to host multiple different GraphQL schemas with different purposes.
In the example above we used only the `default` schema, but the same principle can be replicated and applied to any number of schemas within a single application.

### GraphQLController

Simple version of a controller to execute GraphQL API requests against a given schema. It also includes a actions and templates for a schema overview and GraphiQL integration. It can be extended to alter its functionality (for example by overriding the `getEnabledModules` function) or it can serve as an inspiration to include the functionality in your own controllers.

Create a custom controller in your application which inherits the functionalities from the provided one.

```php
<?php declare(strict_types = 1);

namespace App\Controller\GraphQL;

use App\GraphQL\Default\Schema;
use Graphpinator\Symfony\GraphQLController;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/graphql/default', name: 'graphql_default_')]
final class DefaultGraphQlController extends GraphQLController
{
    public function __construct(Schema $schema, LoggerInterface $logger, CacheItemPoolInterface $cache, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct($schema, $logger, $cache, $urlGenerator);
    }
}

```

The base controller handles 5 actions:

- `OPTIONS` `/` to handle CORS
- `GET` or `POST` `/`  which handles the GraphQL requests
- `GET` `/schema`  which renders the schema in a GraphQL typesystem language
- `GET` `/schema.graphql`  which returns the schema in a GraphQL typesystem language as a file attachment
- `GET` `/ui`  which renders [GraphiQL](https://github.com/graphql/graphiql/tree/main/packages/graphiql#readme), a graphical interface to interact with your schema.

### Adapters

- `\Graphpinator\Symfony\RequestFactory`
    - Implements `RequestFactory` and enables direct creation of `\Graphpinator\Request\Request` from Symfony HTTP abstraction.
- `\Graphpinator\Symfony\FileProvider`
    - Implements `FileProvider` interface needed by `infinityloop-dev/graphpinator-upload` module.
- `\Graphpinator\Symfony\ConstraintDirectiveAccessor`
    - Implements `ConstraintDirectiveAccessor` interface needed by `infinityloop-dev/graphpinator-constraint-directives`.
    - Needs to be manually added to the services.yaml using following configuration:
      ```yaml
      services:
          Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor:
              class: 'Graphpinator\Symfony\ConstraintDirectiveAccessor'
              arguments:
                  $container: '@service_container'
      ```
