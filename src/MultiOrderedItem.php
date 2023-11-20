<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

/**
 * @internal
 */
class MultiOrderedItem
{
    public function __construct(
        public string $id,
        public mixed $item,
        public array $before = [],
        public array $after = [],
        public int $priority = 0,
    ) {}
}
