<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

/**
 * Defines an orderable collection of arbitrary values.
 *
 * Values may be added to the collection at any priority, or relative to an existing value or values.  Internally,
 * this implementation relies on topological (before/after) sorting and will convert priorities to it as needed.
 * Higher priority entries will come "before" lower-priority entries.  The order in which values with the same priority
 * or no relevant before/after rules are returned is explicitly undefined and you should not rely on it.
 *
 * This version is a little slower and more memory-intensive than OrderedCollection,
 * but supports multiple before/after rules on the same object.  It also includes
 * cycle-detection.
 */
class MultiOrderedCollection implements \IteratorAggregate, OrderableCollection
{
    /** @var array<string, MultiOrderedItem>  */
    protected array $items = [];

    /** @var array<string, MultiOrderedItem>  */
    protected array $itemIndex = [];

    /**
     * @var array<int, array<MultiOrderedItem>>
     *
     * The key is the priority, the value is a list of the items at that priority.
     */
    protected array $toTopologize = [];

    /**
     * @var array<string>
     *
     * List of ids of items that use "after" ordering, so will need to be converted
     * to "before" ordering before sorting.
     */
    protected array $toNormalize = [];

    /** @var array<string, mixed>  */
    protected ?array $sorted = null;

    // These three methods are solely for compatibility with OrderedCollection.
    // add() is the real API call.

    public function addItem(mixed $item, int $priority = 0, ?string $id = null): string
    {
        return $this->add($item, $id, $priority);
    }

    public function addItemBefore(string $before, mixed $item, ?string $id = null): string
    {
        return $this->add($item, $id, before: [$before]);
    }

    public function addItemAfter(string $after, mixed $item, ?string $id = null): string
    {
        return $this->add($item, $id, after: [$after]);
    }

    /**
     * Adds an item to the collection.
     *
     * Using this method with named arguments is strongly recommended.
     *
     * Specifying multiple of $priority, $before, and $after is allowed. If none are specified,
     * the item will be ordered at priority 0 so that other entries can rely on that when
     * specifying priority values.
     *
     * @param mixed $item
     *   The item to add. May be any data type.
     * @param string|null $id
     *   An opaque string ID by which this item should be known. If it already exists a counter suffix will be added.
     * @param int|null $priority
     *   A priority.  Higher numbers will sort before lower numbers.
     * @param array<string> $before
     *   A list of other items this item should sort before.
     * @param array<string> $after
     * A list of other items this item should sort after.
     * @return string
     *   An opaque ID string uniquely identifying the item for future reference.
     */
    public function add(mixed $item, ?string $id = null, ?int $priority = null, array $before = [], array $after = []): string
    {
        $id = $this->enforceUniqueId($id);

        if (!$before && !$after) {
            $priority ??= 0;
        }

        $record = new MultiOrderedItem(id: $id, item: $item, before: $before, after: $after, priority: $priority ?? 0);

        if ($after) {
            $this->toNormalize[] = $id;
        }

        if (!is_null($priority)) {
            $this->toTopologize[$priority][$id] = $record;
        } else {
            $this->items[$id] = $record;
        }

        $this->itemIndex[$id] = $record;
        $this->sorted = null;
        return $id;
    }

    public function getIterator(): \ArrayIterator
    {
        $sorted = $this->sorted ??= array_map(fn (string $id): mixed => $this->items[$id]->item, $this->sort());
        return new \ArrayIterator($sorted);
    }

    /**
     * @return array<string>
     *     An array of IDs, in sorted order.
     */
    protected function sort(): array
    {
        $this->normalizeDirection();
        $this->topologizePendingItems();

        // Compute the initial indegrees for all items.
        $indegrees = array_fill_keys(array_keys($this->items), 0);
        foreach ($this->items as $id => $node) {
            foreach ($node->before as $neighbor) {
                if (isset($this->items[$neighbor])) {
                    $indegrees[$neighbor]++;
                }
            }
        }

        // Find items with nothing that comes before it.
        $usableItems = [];
        foreach ($this->items as $id => $item) {
            if ($indegrees[$id] === 0) {
                $usableItems[] = $id;
            }
        }

        // Because the items were pushed onto the usable list, we need
        // to reverse it to get them back in the order they were added.
        $usableItems = array_reverse($usableItems);

        // Keep removing usable items until there are none left.
        $sorted = [];
        while (count($usableItems)) {
            // Grab an available item. We know it's sorted.
            $id = array_pop($usableItems);
            $sorted[] = $id;

            // Decrement the neighbor count of everything that item was before.
            $nowUsable = [];
            foreach ($this->items[$id]->before as $neighbor) {
                if (!isset($indegrees[$neighbor])) {
                    continue;
                }
                $indegrees[$neighbor]--;
                if ($indegrees[$neighbor] === 0) {
                    $nowUsable[] = $neighbor;
                }
            }
            // Technically we don't promise FIFO order, but it's useful,
            // especially for compatibility with OrderedCollection. Since
            // $usableItems is popped above, we need to therefore flip the
            // order of the newly usable items.
            $usableItems = [...$usableItems, ...array_reverse($nowUsable)];
        }

        // We've run out of nodes with no incoming edges.
        // Did we add all the nodes or find a cycle?
        if (count($sorted) === count($this->items)) {
            return $sorted;
        }

        throw CycleFound::for(array_diff(array_keys($this->items), $sorted));
    }

    protected function topologizePendingItems(): void
    {
        // First, put the priorities in order, low numbers first.
        ksort($this->toTopologize);

        while (count($this->toTopologize)) {
            // Get the highest priority set.  That's the last item in the
            // list, which is fastest to access.
            $items = array_pop($this->toTopologize);

            // We don't actually care what the next priority is, but need it
            // as a lookup value to get the items in that priority.
            $otherPriority = array_key_last($this->toTopologize);

            // If $otherPriority is null, it means this is the last priority set
            // so there is nothing else it comes before.
            if (!is_null($otherPriority)) {
                $next = array_map(static fn(MultiOrderedItem $i)
                    => $i->id, $this->toTopologize[$otherPriority]);

                /** @var MultiOrderedItem $item */
                foreach ($items as $item) {
                    $item->before = array_unique([...$item->before, ...$next]);
                    $this->items[$item->id] = $item;
                }
            } else {
                foreach ($items as $item) {
                    $this->items[$item->id] = $item;
                }
            }
        }
    }

    /**
     * Ensures a unique ID for all items in the collection.
     *
     * @param string|null $id
     *   The proposed ID of an item, or null to generate a random string.
     *
     * @return string
     *   A confirmed unique ID string.
     */
    protected function enforceUniqueId(?string $id): string
    {
        $candidateId = $id ?? uniqid('', true);

        $counter = 1;
        while (isset($this->itemIndex[$candidateId])) {
            $candidateId = $id . '-' . $counter++;
        }

        return $candidateId;
    }

    /**
     * Convert all records to use `before`, not `after`, for consistency.
     */
    protected function normalizeDirection(): void
    {
        /** @var MultiOrderedItem $node */
        foreach ($this->toNormalize as $id) {
            foreach ($this->itemIndex[$id]->after ?? [] as $afterId) {
                // If this item should come after something that doesn't exist,
                // that's the same as no restrictions.
                if (isset($this->itemIndex[$afterId])) {
                    $this->itemIndex[$afterId]->before[] = $id;
                }
            }
        }
        $this->toNormalize = [];
    }
}
