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

use RuntimeException;
use Throwable;

class TwitterApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatusCode = 0,
        public readonly string $responseBody = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatusCode, $previous);
    }
}
