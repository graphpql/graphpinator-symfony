<?php

declare(strict_types = 1);

namespace Graphpinator\Symfony;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class GraphpinatorBundle extends AbstractBundle
{
    #[\Override]
    public function getPath() : string
    {
        return \dirname(__DIR__);
    }
}
