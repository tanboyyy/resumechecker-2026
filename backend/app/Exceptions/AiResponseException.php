<?php

namespace App\Exceptions;

/**
 * The analysis service answered, but the answer was unusable and retrying the
 * identical request would not change that.
 */
class AiResponseException extends \RuntimeException
{
}
