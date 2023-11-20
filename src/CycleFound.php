<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

use Throwable;

class CycleFound extends \RuntimeException
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Graph has a cycle! No topological ordering exists.", $code, $previous);
    }
}
