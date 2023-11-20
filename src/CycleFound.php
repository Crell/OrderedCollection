<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

class CycleFound extends \RuntimeException
{
    /** @var array<string> */
    public readonly array $ids;

    public static function for(array $ids): self
    {
        $new = new self();

        $new->ids = $ids;
        $new->message = "Cycle detected involving entries: " . implode(', ', $ids);

        return $new;
    }
}
