<?php

declare(strict_types=1);

namespace Crell\OrderedCollection\Benchmarks;

use Crell\OrderedCollection\MultiOrderedCollection;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\OutputTimeUnit;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Groups(['MultiSort'])]
#[Revs(10), Iterations(3), Warmup(2), RetryThreshold(5)]
#[OutputTimeUnit('milliseconds', 3)]
class MultiOrderedCollectionBench
{
    protected const Prefix = 'A';

    protected const DataSize = 20_000;


    protected const RandomPriorityMax = self::DataSize;

    #[ParamProviders('collections')]
    public function benchSort(array $params): void
    {
        // Trigger sorting. That's all we care about doing.
        $params['collection']->getIterator();
    }

    public function collections(): iterable
    {
        yield ['collection' => $this->allPriority()];
        yield ['collection' => $this->allTopologicalBefore()];
        yield ['collection' => $this->allTopologicalAfter()];
        yield ['collection' => $this->mixedOrdering()];
    }

    protected function allPriority(): MultiOrderedCollection
    {
        $c = new MultiOrderedCollection();

        for ($i = 0; $i < self::DataSize; ++$i) {
            $c->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                priority: \random_int(-1 * self::RandomPriorityMax, self::RandomPriorityMax),
            );
        }
        return $c;
    }

    protected function allTopologicalBefore(): MultiOrderedCollection
    {
        $c = new MultiOrderedCollection();

        for ($i = 0; $i < self::DataSize; ++$i) {
            $c->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                before: [$i === 0 ? null : (self::Prefix . \random_int(0, $i - 1))],
            );
        }

        return $c;
    }

    protected function allTopologicalAfter(): MultiOrderedCollection
    {
        $c = new MultiOrderedCollection();

        for ($i = 0; $i < self::DataSize; ++$i) {
            $c->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                after: [$i === 0 ? null : (self::Prefix . \random_int(0, $i - 1))],
            );
        }

        return $c;
    }

    public function mixedOrdering(): MultiOrderedCollection
    {
        $c = new MultiOrderedCollection();

        for ($i = 0; $i < self::DataSize; ++$i) {
            if ($i % 2) {
                $c->add(
                    item: self::Prefix . $i,
                    id: self::Prefix . $i,
                    priority: \random_int(0, self::RandomPriorityMax),
                );
            }
            $c->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                before: [$i === 0 ? null : (self::Prefix . \random_int(0, $i - 1))],
            );
        }

        return $c;
    }
}
