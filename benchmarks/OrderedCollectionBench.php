<?php

declare(strict_types=1);

namespace Crell\OrderedCollection\Benchmarks;

use Crell\OrderedCollection\OrderedCollection;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\OutputTimeUnit;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Groups(['Collection'])]
#[Revs(10), Iterations(3), Warmup(2), RetryThreshold(10)]
#[BeforeMethods(['setUp']), AfterMethods(['tearDown'])]
#[OutputTimeUnit('milliseconds', 3)]
class OrderedCollectionBench
{
    public function setUp(): void {}

    public function tearDown(): void {}

    public function provideItems(): iterable
    {
        foreach ([1, 20, 50, 100, 500] as $count) {
            yield array_fill(1, $count, 'a');
        }
    }

    #[ParamProviders('provideItems')]
    public function bench_populate_ordered_collection(array $items): void
    {
        $collection = new OrderedCollection();

        foreach ($items as $item) {
            $collection->addItem($item);
        }
    }
}
