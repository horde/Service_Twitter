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

use Horde\OAuth\Client\ProviderConfig;

final class TwitterProviderEndpoints
{
    public static function create(): ProviderConfig
    {
        return new ProviderConfig(
            issuer: 'https://twitter.com',
            authorizationEndpoint: 'https://twitter.com/i/oauth2/authorize',
            tokenEndpoint: 'https://api.twitter.com/2/oauth2/token',
            revocationEndpoint: 'https://api.twitter.com/2/oauth2/revoke',
            scopesSupported: [
                'tweet.read',
                'tweet.write',
                'users.read',
                'bookmark.read',
                'bookmark.write',
                'like.read',
                'like.write',
                'offline.access',
            ],
        );
    }
}
