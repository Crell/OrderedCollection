<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

class OrderedItem
{
    public string $before;

    public string $after;

    final public function __construct(
        public mixed $item = null,
        public int $priority = 0,
        public string $id = '') {}

    public static function createWithPriority(mixed $item, int $priority, string $id): self
    {
        $new = new static();
        $new->item = $item;
        $new->priority = $priority;
        $new->id = $id;

        return $new;
    }

    public static function createBefore(mixed $item, string $pivotId, string $id): self
    {
        $new = new static();
        $new->item = $item;
        $new->before = $pivotId;
        $new->id = $id;

        return $new;
    }

    public static function createAfter(mixed $item, string $pivotId, string $id): self
    {
        $new = new static();
        $new->item = $item;
        $new->after = $pivotId;
        $new->id = $id;

        return $new;
    }
}
