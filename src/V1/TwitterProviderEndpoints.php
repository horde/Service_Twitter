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

use Horde\OAuth\V10a\Client\ProviderEndpoints;

final class TwitterProviderEndpoints
{
    public static function create(): ProviderEndpoints
    {
        return new ProviderEndpoints(
            requestTokenUrl: 'https://api.twitter.com/oauth/request_token',
            authorizeUrl: 'https://api.twitter.com/oauth/authorize',
            accessTokenUrl: 'https://api.twitter.com/oauth/access_token',
        );
    }
}
