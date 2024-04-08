# Ordered Collection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Ordered Collection is what it says on the tin; it's a flexible tool for ordering arbitrary items based on either priority values or topological sorting (before/after).  It contains two implementations, `OrddredCollection` and `MultiOrderedCollection`.  The former is a bit faster, while the latter is considerably more powerful.

For more on Priority and Topological sorting, see [this benchmark blog post](https://peakd.com/hive-168588/@crell/extrinsic-sorting-benchmark) comparing the results.

## `OrderedCollection`

`OrderedCollection` supports ordering items by integer priorities, or a single before/after value.  Internally, it converts the before/after information into priority values, and then sorts the whole list by priority.  This is often faster than sorting topologically, but does not handle more than a single before/after entry per item, and cannot detect cyclic dependencies in the before/after ordering.

To use it first, create a new collection:

```php
use Crell\OrderedCollection\OrderedCollection;

$collection = new OrderedCollection();
```

Now, arbitrary items may be added to the collection with `addItem()`, `addItemBefore()`, and `addItemAfter()`.  Each item may be an arbitrary value, and they do not need to be of the same type.

When adding an item, you may provide an `$id`.  If you do not, one will be created on the fly.  The ID that was used will be returned by the `addItem*()` method.  The ID is necessary for before/after ordering.  If the ID is already in use, a numeric suffix will be added automatically to ensure uniqueness.

```php
// Adds an item with priority 3, and a random ID will be generated.
$kirk = $collection->addItem('James T. Kirk', 3);

// Adds an item with priority 5, and an ID of "picard".
$picard = $collection->addItem('Jean-Luc Picard', 5, 'picard');

// Adds an item to some somewhere after another item, by its ID. 
// The ID for it will be auto-generated
$sisko = $collection->addItemAfter($picard, 'Benjamin Sisko');

// Adds an item to some somewhere before another item, by its ID.
// The new item's ID will be "janeway".
$janeway = $collection->addItemBefore($kirk, 'Katheryn Janeway', 'janeway');
```

Once the items are added, they will be sorted automatically the first time the collection is iterated.  `OrderedCollection` implements `\IteratorAggregate`, so you can use either `foreach()` or `iterator_to_array()` to get values back out.

```php
foreach ($collection as $item) {
    print $item . PHP_EOL;
}
```

In this case, would give the following output:

```text
Katheryn Janeway
Jean-Luc Picard
Benjamin Sisko
James T. Kirk
```

## `MultiOrderedCollection`

This second, more robust option is very similar to `OrderedCollection`, and both implement the same `OrderableCollection` interface.  However, `MultiOrderedCollection` has an additional method, `add()`, that can handle priority, before, and after ordering, and supports multiple before/after entries on the same item.  For that reason, using `add()` with `MultiOrderedCollection` is recommended, though the common interface will still work on both.

`MultiOrderedCollection` converts all priorities into "before" entries, and then sorts the collection topologically.  This can be a little bit slower, but it supports multiple before/after directives on a single item and will also detect circular dependencies, which triggers a `CycleFound` exception.

The `add()` method's signature is like so:

```php
add(
    mixed $item,
    ?string $id = null,
    ?int $priority = null,
    array $before = [],
    array $after = [],
): string
```

The return value is the ID that was assigned to the value, which may then be used in before/after ordering.  Of note, the `$before` and `$after` parameters take an array, not a single ID.  Also, because there are so many options, using named arguments is strongly recommended.

The example above, on `MultiOrderedCollection`, would look like this:

```php
use Crell\OrderedCollection\MultiOrderedCollection;

$collection = new MultiOrderedCollection();

// Adds an item with priority 3, and a random ID will be generated.
$kirk = $collection->add('James T. Kirk', priority: 3);

// Adds an item with priority 3, and an ID of "picard".
$picard = $collection->add('Jean-Luc Picard', priority: 5, id: 'picard');

// Adds an item to some somewhere after another item, by its ID.
// The ID for it will be auto-generated
$sisko = $collection->add('Benjamin Sisko', after: ['picard']);

// Adds an item to some somewhere before another item, by its ID.
// The new item's ID will be "janeway".
$janeway = $collection->add('Katheryn Janeway', before: [$kirk], id: 'janeway');

foreach ($collection as $item) {
    print $item . PHP_EOL;
}
```

In this case, the output would be the same as before.

```text
Katheryn Janeway
Jean-Luc Picard
Benjamin Sisko
James T. Kirk
```

## Guarantees

`OrderedCollection` and `MultiOrderedCollection` provide the following guarantees about the resulting list of values 
returned:

* Any item with a higher priority integer will come before an item with a lower priority integer.
* Any item listed as "before" another item will come before that item.
* Any item listed as "after" another item will come after that item.

They do not provide the following guarantees, so while they may happen you should not count on them.

 * An item that comes "before" another may not come immediately before.  There could still be other items that come between them.
 * An item that comes "after" another may not come immediately after.  There could still be other items that come between them.
 * The order in which items are added is irrelevant.  With `OrderedCollection`, items that have the same priority 
   will *usually* be returned in the order in which they were added, but that is not guaranteed.  With `MultiOrderedCollection`, they will most likely not be returned in the order they were added.  In short, if you care about the order at all, specify it explicitly.

## Types

The items being sorted are not used at all during the process.  While the example above shows strings, you can use arrays, objects, strings, numbers, or whatever else you'd like.  They may be of the same type, or different types.  The collection objects don't care.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form](https://github.com/Crell/OrderedCollection/security) rather than the issue queue.

## Credits

- [Larry Garfield][link-author]
- [All Contributors][link-contributors]

## License

The Lesser GPL version 3 or later. Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Crell/OrderedCollection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/License-LGPLv3-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Crell/OrderedCollection.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/crell/ordered-collection
[link-downloads]: https://packagist.org/packages/crell/ordered-collection
[link-author]: https://github.com/Crell
[link-contributors]: ../../contributors
