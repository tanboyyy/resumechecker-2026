<?php

namespace App\Jobs;

/**
 * Thrown when a resume file cannot be read and retrying will not help.
 *
 * The message is shown directly to the user, so it must explain what to do next.
 */
class UnreadableResumeException extends \RuntimeException
{
}
