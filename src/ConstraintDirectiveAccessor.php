<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor as ConstraintDirectiveAccessorContract;
use Graphpinator\ConstraintDirectives\FloatConstraintDirective;
use Graphpinator\ConstraintDirectives\IntConstraintDirective;
use Graphpinator\ConstraintDirectives\ListConstraintDirective;
use Graphpinator\ConstraintDirectives\ListConstraintInput;
use Graphpinator\ConstraintDirectives\ObjectConstraintDirective;
use Graphpinator\ConstraintDirectives\ObjectConstraintInput;
use Graphpinator\ConstraintDirectives\StringConstraintDirective;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ConstraintDirectiveAccessor implements ConstraintDirectiveAccessorContract
{
    public function __construct(
        private ContainerInterface $container,
    )
    {
    }

    #[\Override]
    public function getString() : StringConstraintDirective
    {
        return $this->container->get(StringConstraintDirective::class);
    }

    #[\Override]
    public function getInt() : IntConstraintDirective
    {
        return $this->container->get(IntConstraintDirective::class);
    }

    #[\Override]
    public function getFloat() : FloatConstraintDirective
    {
        return $this->container->get(FloatConstraintDirective::class);
    }

    #[\Override]
    public function getList() : ListConstraintDirective
    {
        return $this->container->get(ListConstraintDirective::class);
    }

    #[\Override]
    public function getListInput() : ListConstraintInput
    {
        return $this->container->get(ListConstraintInput::class);
    }

    #[\Override]
    public function getObject() : ObjectConstraintDirective
    {
        return $this->container->get(ObjectConstraintDirective::class);
    }

    #[\Override]
    public function getObjectInput() : ObjectConstraintInput
    {
        return $this->container->get(ObjectConstraintInput::class);
    }
}
