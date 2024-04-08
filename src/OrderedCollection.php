<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

/**
 * Defines an orderable collection of arbitrary values.
 *
 * Values may be added to the collection at any priority, or relative to an existing value.  When iterated they will
 * be returned in priority order with higher priority values being returned first.  The order in which values with the
 * same priority are returned is explicitly undefined and you should not rely on it.  (Although in practice it should be
 * FIFO, that is not guaranteed.)
 *
 * This version is a little bit faster than MultiOrderedCollection, but
 * does not support specifying more than one before/after relationship per
 * entry.
 */
class OrderedCollection implements \IteratorAggregate, OrderableCollection
{
    /**
     * @var array<int, array<OrderedItem>>
     *
     * An indexed array of arrays of Item entries. The key is the priority, the value is an array of Items.
     */
    protected array $items = [];

    /**
     * @var array<OrderedItem>
     *
     * A list of the items in the collection indexed by ID. Order is undefined.
     */
    protected array $itemLookup = [];

    protected bool $sorted = false;

    /** @var array<OrderedItem> */
    protected array $toPrioritize = [];

    public function addItem(mixed $item, int $priority = 0, ?string $id = null): string
    {
        $id = $this->enforceUniqueId($id);

        $item = OrderedItem::createWithPriority($item, $priority, $id);

        $this->items[$priority][] = $item;
        $this->itemLookup[$id] = $item;

        $this->sorted = false;

        return $id;
    }

    public function addItemBefore(string $before, mixed $item, ?string $id = null): string
    {
        $id = $this->enforceUniqueId($id);

        // If this new item is pivoting off of is already defined, add it normally.
        if (isset($this->itemLookup[$before])) {
            // Because high numbers come first, we have to ADD one to get the new item to be returned first.
            return $this->addItem($item, $this->itemLookup[$before]->priority + 1, $id);
        }

        // Otherwise, we still add it but flag it as one to revisit later to determine the priority.
        $item = OrderedItem::createBefore($item, $before, $id);

        $this->toPrioritize[] = $item;
        $this->itemLookup[$id] = $item;

        $this->sorted = false;

        return $id;
    }

    public function addItemAfter(string $after, mixed $item, ?string $id = null): string
    {
        $id = $this->enforceUniqueId($id);

        // If the item this new item is pivoting off of is already defined, add it normally.
        if (isset($this->itemLookup[$after])) {
            // Because high numbers come first, we have to SUBTRACT one to get the new item to be returned first.
            return $this->addItem($item, $this->itemLookup[$after]->priority - 1, $id);
        }

        // Otherwise, we still add it but flag it as one to revisit later to determine the priority.
        $item = OrderedItem::createAfter($item, $after, $id);

        $this->toPrioritize[] = $item;
        $this->itemLookup[$id] = $item;

        $this->sorted = false;

        return $id;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Traversable<mixed>
     *
     * Note: Because of how iterator_to_array() works, you MUST pass `false` as the second parameter to that function
     * if calling it on the return from this object.  If not, only the last list's worth of values will be included in
     * the resulting array.
     */
    public function getIterator(): \Traversable
    {
        if (!$this->sorted) {
            $this->prioritizePendingItems();
            krsort($this->items);
            $this->sorted = true;
        }

        return (function () {
            foreach ($this->items as $itemList) {
                yield from array_map(static function (OrderedItem $item) {
                    return $item->item;
                }, $itemList);
            }
        })();
    }

    protected function prioritizePendingItems(): void
    {
        /** @var OrderedItem $item */
        foreach ($this->toPrioritize as $item) {
            if (isset($item->before)) {
                $priority = isset($this->itemLookup[$item->before])
                    ? $this->itemLookup[$item->before]->priority + 1
                    : 0;
                $this->items[$priority][] = $item;
            } elseif (isset($item->after)) {
                $priority = isset($this->itemLookup[$item->after])
                    ? $this->itemLookup[$item->after]->priority - 1
                    : 0;
                $this->items[$priority][] = $item;
            } else {
                throw new \Error('No, seriously, how did you get here?');
            }
        }

        // We never need to reprioritize these again.
        $this->toPrioritize = [];
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
        while (isset($this->itemLookup[$candidateId])) {
            $candidateId = $id . '-' . $counter++;
        }

        return $candidateId;
    }
}
