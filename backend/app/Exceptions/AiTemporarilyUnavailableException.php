<?php

namespace App\Exceptions;

/**
 * The analysis service was reachable but could not serve this request right now.
 *
 * Retrying is worthwhile, so jobs let this propagate to the queue.
 */
class AiTemporarilyUnavailableException extends \RuntimeException
{
}
