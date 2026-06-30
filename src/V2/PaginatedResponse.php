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

namespace Horde\Service\Twitter\V2;

use Closure;
use Generator;

/**
 * One page of a paginated v2 response, plus the optional `includes` envelope
 * the API ships when expansions are requested.
 *
 * `iterator()` walks every item across pages: it yields the current page's
 * data first, then — if the caller supplied a `fetcher` closure at
 * construction time and `meta->nextToken` is set — invokes the closure with
 * the next pagination token to fetch the following page, and so on until
 * Twitter stops returning a next_token. The fetcher is the only coupling
 * point with `TwitterApiClient`; closing over the right method + arguments
 * lives there.
 *
 * @template T
 */
final class PaginatedResponse
{
    /**
     * @param list<T> $data
     * @param Closure(string): self<T>|null $fetcher closure that takes a
     *        pagination_token and returns the next PaginatedResponse, or null
     *        when no auto-pagination is wanted
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationMeta $meta,
        public readonly ?Includes $includes = null,
        private readonly ?Closure $fetcher = null,
    ) {}

    /**
     * Yield every item across every page. Stops as soon as a page has no
     * next_token, or — if no fetcher was wired — after the first page. Each
     * fetched page is discarded after iteration so this stays bounded in
     * memory even for very large timelines.
     *
     * @return Generator<int, T>
     */
    public function iterator(): Generator
    {
        $page = $this;
        $index = 0;
        while (true) {
            foreach ($page->data as $item) {
                yield $index++ => $item;
            }
            if ($this->fetcher === null || !$page->meta->hasNextPage()) {
                return;
            }
            $next = ($this->fetcher)((string) $page->meta->nextToken);
            $page = $next;
        }
    }
}
