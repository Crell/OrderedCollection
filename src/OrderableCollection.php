<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

interface OrderableCollection extends \Traversable
{
    /**
     * Adds an item to the collection with a given priority.  (Higher numbers come first.)
     *
     * @param mixed $item
     *   The item to add. May be any data type.
     * @param int $priority
     *   The priority order of the item. Higher numbers will come first.
     * @param ?string $id
     *   An opaque string ID by which this item should be known. If it already exists a counter suffix will be added.
     *
     * @return string
     *   An opaque ID string uniquely identifying the item for future reference.
     */
    public function addItem(mixed $item, int $priority = 0, ?string $id = null): string;

    /**
     * Adds an item to the collection before another existing item.
     *
     * Note: The new item is only guaranteed to get returned before the existing item. No guarantee is made
     * regarding when it will be returned relative to any other item.
     *
     * @param string $before
     *   The existing ID of an item in the collection.
     * @param mixed $item
     *   The new item to add.
     * @param ?string $id
     *   An opaque string ID by which this item should be known. If it already exists a counter suffix will be added.
     *
     * @return string
     *   An opaque ID string uniquely identifying the new item for future reference.
     */
    public function addItemBefore(string $before, mixed $item, ?string $id = null): string;

    /**
     * Adds an item to the collection after another existing item.
     *
     * Note: The new item is only guaranteed to get returned after the existing item. No guarantee is made
     * regarding when it will be returned relative to any other item.
     *
     * @param string $after
     *   The existing ID of an item in the collection.
     * @param mixed $item
     *   The new item to add.
     * @param ?string $id
     *   An opaque string ID by which this item should be known. If it already exists a counter suffix will be added.
     *
     * @return string
     *   An opaque ID string uniquely identifying the new item for future reference.
     */
    public function addItemAfter(string $after, mixed $item, ?string $id = null): string;

}
