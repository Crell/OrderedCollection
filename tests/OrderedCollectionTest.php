<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrderedCollectionTest extends TestCase
{
    #[Test]
    public function can_add_items_with_same_priority() : void
    {
        $c = new OrderedCollection();
        $c->addItem('A', 1);
        $c->addItem('B', 1);
        $c->addItem('C', 1);

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        $this->assertEquals('ABC', implode($results));
    }

    #[Test]
    public function can_add_items_with_different_priority() : void
    {
        $c = new OrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 1);
        $c->addItem('B', 2);
        $c->addItem('A', 3);

        $results = iterator_to_array($c, false);

        $this->assertEquals('ABC', implode($results));
    }

    #[Test]
    public function can_add_items_with_same_and_different_priority() : void
    {
        $c = new OrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 2);
        $c->addItem('B', 3);
        $c->addItem('A', 4);
        $c->addItem('D', 1);
        $c->addItem('E', 1);
        $c->addItem('F', 1);

        $results = iterator_to_array($c, false);

        $this->assertEquals('ABCDEF', implode($results));
    }

    #[Test]
    public function can_add_items_before_other_items() : void
    {
        $c = new OrderedCollection();
        // High priority number comes first.
        $cid = $c->addItem('C', 2);
        $c->addItem('D', 1);
        $c->addItem('A', 3);

        $c->addItemBefore($cid, 'B');

        $results = implode(iterator_to_array($c, false));

        $this->assertTrue(strpos($results, 'B') < strpos($results, 'C'));
    }

    #[Test]
    public function can_add_items_after_other_items() : void
    {
        $c = new OrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 2);
        $c->addItem('D', 1);
        $aid = $c->addItem('A', 3);

        $c->addItemAfter($aid, 'B');

        $results = implode(iterator_to_array($c, false));

        $this->assertTrue(strpos($results, 'B') > strpos($results, 'A'));
    }

    #[Test]
    public function explicit_id_works() : void
    {
        $c = new OrderedCollection();
        $a = $c->addItem('A', 1, 'item_a');
        $c->addItemAfter('item_a', 'B');

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        $this->assertEquals('AB', implode($results));
    }

    #[Test]
    public function explicit_id_that_already_exists_works() : void
    {
        $c = new OrderedCollection();
        $a = $c->addItem('A', 1, 'an_item');
        $b = $c->addItem('B', 1, 'an_item');
        $c->addItemAfter($b, 'C');

        $this->assertNotEquals($a, $b);

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        $this->assertEquals('ABC', implode($results));
    }

    #[Test]
    public function adding_out_of_order_works() : void
    {
        $c = new OrderedCollection();

        // Add C to come after B, but B isn't defined yet.
        $c->addItemAfter('b', 'C', 'c');

        // Add A to come before B, but B isn't defined yet.
        $c->addItemBefore('b', 'A', 'a');

        // Now define B.
        $c->addItem('B', 3, 'b');

        $results = iterator_to_array($c, false);

        $this->assertEquals('ABC', implode($results));
    }

    #[Test]
    public function adding_relative_to_non_existing_item_fails() : void
    {
        $this->expectException(MissingItemException::class);

        $c = new OrderedCollection();

        // Add A to come before B, but B isn't defined.
        $c->addItemBefore('b', 'A', 'a');

        // This should throw an exception since B doesn't exist.
        iterator_to_array($c, false);
    }
}
