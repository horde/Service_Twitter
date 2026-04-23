<?php

declare(strict_types=1);

/**
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @license http://www.horde.org/licenses/bsd BSD
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 */

namespace Horde\Service\Twitter\V1;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Tweet>
 */
class TweetList implements IteratorAggregate, Countable
{
    /** @param array<Tweet> $tweets */
    public function __construct(
        private readonly array $tweets = [],
    ) {}

    /** @return Traversable<int, Tweet> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tweets);
    }

    public function count(): int
    {
        return count($this->tweets);
    }

    /** @return array<Tweet> */
    public function toArray(): array
    {
        return $this->tweets;
    }
}
