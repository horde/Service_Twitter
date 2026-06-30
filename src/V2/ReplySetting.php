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

/**
 * Valid values for the `reply_settings` field on POST /2/tweets and on the
 * Tweet response. Twitter accepts the API names directly; the enum exists so
 * callers do not have to memorise the spelling.
 */
enum ReplySetting: string
{
    case Everyone = 'everyone';
    case MentionedUsers = 'mentionedUsers';
    case Following = 'following';
    case Subscribers = 'subscribers';
}
