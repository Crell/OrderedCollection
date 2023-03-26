# Ordered Collection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Ordered Collection is what it says on the tin; it's a flexible tool for ordering arbitrary items based on either priority values or topological sorting (before/after).

For more on Priority and Topological sorting, see [this benchmark blog post](https://peakd.com/hive-168588/@crell/extrinsic-sorting-benchmark) comparing the results.  Ordered Collection uses the approach found most performant in that test.

## Usage

First, create a new collection:

```php
use Crell\OrderedCollection\OrderedCollection;

$collection = new OrderedCollection();
```

Now, arbitrary items may be added to the collection with `addItem()`, `addItemBefore()`, and `addItemAfter()`.  Each item may be an arbitrary value, and they do not need to be of the same type.

When adding an item, you may provide an `$id`.  If you do not, one will be created on the fly.  The ID that was used will be returned by the `addItem*()` method.  The ID is necessary for before/after ordering.  If the ID is already in use, a numeric suffix will be added automatically to ensure uniqueness.

```php
// Adds an item with priority 3, and a random ID will be generated.
$kirk = $collection->addItem('James T. Kirk', 3);

// Adds an item with priority 3, and an ID of "picard".
$picard = $collection->addItem('Jean-Luc Picard', 5, 'picard');

// Adds an item to some somewhere after another item, by its ID. 
// The ID for it will be auto-generated
$sisko = $collection->addItemAfter($picard, 'Benjamin Sisko');

// Adds an item to some somewhere before another item, by its ID.
// The new item's ID will be "janeway".
$janeway = $collection->addItemBefore($kirk, 'Katheryn Janeway');
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

## Guarantees

`OrderedCollection` provides the following guarantees about the resulting list of values returned:

* Any item with a higher priority integer will come before an item with a lower priority integer.
* Any item listed as "before" another item will come before that item.
* Any item listed as "after" another item will come after that item.

It does not provide the following guarantees, so while they may happen you should not count on them.

 * An item that comes "before" another may not come immediately before.  There could still be other items that come between them.
 * An item that comes "after" another may not come immediately after.  There could still be other items that come between them.
 * Items that have the same priority will *usually* be returned in the order in which they were added, but that is not guaranteed, and you should not rely on it.

## Types

The items being sorted are not used at all during the process.  While the example above shows strings, you can use arrays, objects, strings, numbers, or whatever else you'd like.  They may be of the same type, or different types.  `OrderedCollection` doesn't care.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email larry at garfieldtech dot com instead of using the issue tracker.

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
