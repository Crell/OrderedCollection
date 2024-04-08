<?php

declare(strict_types=1);

namespace Crell\OrderedCollection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MultiOrderedCollectionTest extends TestCase
{
    #[Test]
    public function can_add_items_with_same_priority(): void
    {
        $c = new MultiOrderedCollection();
        $c->addItem('A', 1);
        $c->addItem('B', 1);
        $c->addItem('C', 1);

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        self::assertEquals('ABC', implode($results));
    }

    #[Test]
    public function can_add_items_with_different_priority(): void
    {
        $c = new MultiOrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 1);
        $c->addItem('B', 2);
        $c->addItem('A', 3);

        $results = iterator_to_array($c, false);

        self::assertEquals('ABC', implode($results));
    }

    #[Test]
    public function can_add_items_with_same_and_different_priority(): void
    {
        $c = new MultiOrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 2, 'C');
        $c->addItem('B', 3, 'B');
        $c->addItem('A', 4, 'A');
        $c->addItem('D', 1, 'D');
        $c->addItem('E', 1, 'E');
        $c->addItem('F', 1, 'F');

        $results = iterator_to_array($c, false);

        self::assertEquals('ABCDEF', implode($results));
    }

    #[Test]
    public function can_add_items_before_other_items(): void
    {
        $c = new MultiOrderedCollection();
        // High priority number comes first.
        $cid = $c->addItem('C', 2);
        $c->addItem('D', 1);
        $c->addItem('A', 3);

        $c->addItemBefore($cid, 'B');

        $results = implode(iterator_to_array($c, false));

        self::assertTrue(strpos($results, 'B') < strpos($results, 'C'));
    }

    #[Test]
    public function can_add_items_after_other_items(): void
    {
        $c = new MultiOrderedCollection();
        // High priority number comes first.
        $c->addItem('C', 2, 'C');
        $c->addItem('D', 1, 'D');
        $aid = $c->addItem('A', 3, 'A');

        $c->addItemAfter($aid, 'B', 'B');

        $results = implode(iterator_to_array($c, false));

        self::assertTrue(strpos($results, 'B') > strpos($results, 'A'));
    }

    #[Test]
    public function explicit_id_works(): void
    {
        $c = new MultiOrderedCollection();
        $a = $c->addItem('A', 1, 'item_a');
        $c->addItemAfter('item_a', 'B');

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        self::assertEquals('AB', implode($results));
    }

    #[Test]
    public function explicit_id_that_already_exists_works(): void
    {
        $c = new MultiOrderedCollection();
        $a = $c->addItem('A', 1, 'an_item');
        $b = $c->addItem('B', 1, 'an_item');
        $c->addItemAfter($b, 'C');

        $this->assertNotEquals($a, $b);

        // Because the collection uses a generator in the getIterator() method, we have to explicitly ignore the
        // keys in iterator_to_array() or later values will overwrite earlier ones.
        $results = iterator_to_array($c, false);

        self::assertEquals('ABC', implode($results));
    }

    #[Test]
    public function adding_out_of_order_works(): void
    {
        $c = new MultiOrderedCollection();

        // Add C to come after B, but B isn't defined yet.
        $c->addItemAfter('b', 'C', 'c');

        // Add A to come before B, but B isn't defined yet.
        $c->addItemBefore('b', 'A', 'a');

        // Now define B.
        $c->addItem('B', 3, 'b');

        $results = iterator_to_array($c, false);

        self::assertEquals('ABC', implode($results));
    }

    #[Test]
    public function adding_relative_to_non_existing_item_works(): void
    {
        $c = new MultiOrderedCollection();

        // Add A to come before B, but B isn't defined.
        $c->addItemBefore('b', 'A', 'a');

        $results = iterator_to_array($c, false);

        self::assertEquals('A', implode($results));
    }

    #[Test]
    public function adding_multiple_before_after_directives_works(): void
    {
        $c = new MultiOrderedCollection();

        $cid = $c->addItem('C');
        $aid = $c->addItemBefore($cid, 'A');
        $bid = $c->add('B', before: [$cid], after: [$aid]);

        $results = iterator_to_array($c, false);

        self::assertEquals('ABC', implode($results));
    }

    #[Test]
    public function adding_a_cyclic_dependency_throws(): void
    {
        $this->expectException(CycleFound::class);
        $this->expectExceptionMessage("Cycle detected involving entries: C, A, B");

        $c = new MultiOrderedCollection();

        $cid = $c->addItem('C', id: 'C');
        $aid = $c->addItemBefore($cid, 'A', id: 'A');
        $bid = $c->add('B', id: 'B', before: [$aid], after: [$cid]);

        iterator_to_array($c, false);
    }

    #[Test]
    public function zero_priority_works_correctly(): void
    {
        $c = new MultiOrderedCollection();

        $c->addItem('C', 1, 'C');
        $c->addItem('A', priority: 0, id: 'A');
        $c->addItem('B', -1, 'B');

        $results = iterator_to_array($c, false);

        self::assertEquals('CAB', implode($results));
    }

    #[Test]
    public function compound_ordering_works_1(): void
    {
        $c = new MultiOrderedCollection();

        // The un-ordered items should get converted to priority 0.
        $c->add('A');
        $bid = $c->add('B', priority: 90);
        $c->add('C', priority: -5);
        $c->add('D', before: [$bid]);
        $c->add('E');

        $result = implode(iterator_to_array($c, false));

        self::assertTrue(strpos($result, 'B') < strpos($result, 'C'));
        self::assertTrue(strpos($result, 'D') < strpos($result, 'B'));
        self::assertTrue(strpos($result, 'B') < strpos($result, 'E'));
        self::assertTrue(strpos($result, 'E') < strpos($result, 'C'));
    }

    #[Test]
    public function compound_ordering_works_2(): void
    {
        $c = new MultiOrderedCollection();

        $c->add('A', priority: 3);
        $c->add('B', priority: 4, before: ['C']);
        $c->add('C', priority: 2);

        $result = implode(iterator_to_array($c, false));

        self::assertEquals('BAC', $result);
    }
}
