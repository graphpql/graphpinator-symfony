# Graphpinator Symfony [![PHP](https://github.com/infinityloop-dev/graphpinator-symfony/workflows/PHP/badge.svg?branch=master)](https://github.com/infinityloop-dev/graphpinator-symfony/actions?query=workflow%3APHP) [![codecov](https://codecov.io/gh/infinityloop-dev/graphpinator-symfony/branch/master/graph/badge.svg)](https://codecov.io/gh/infinityloop-dev/graphpinator-symfony)

:zap::globe_with_meridians::zap: Graphpinator adapters and addons for Symfony framework.

## Introduction

This package includes adapters for various Graphpinator functionalities and a SchemaPresenter, which returns a response with generated GraphQL type language document.

## Installation

Install package using composer

```composer require graphpql/graphpinator-symfony```

## How to use

### ApiPresenter


### SchemaPresenter



### GraphiQLPresenter



### Cyclic dependendencies


### Multiple schemas



### Adapters

- `\Graphpinator\Symfony\RequestFactory`
    - Implements `RequestFactory` and enables direct creation of `\Graphpinator\Request\Request` from Symfony HTTP abstraction.
- `\Graphpinator\Symfony\FileProvider`
    - Implements `FileProvider` interface needed by `infinityloop-dev/graphpinator-upload` module.
