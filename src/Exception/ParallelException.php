<?php

declare(strict_types=1);

namespace ComposerRunParallel\Exception;

use RuntimeException;

final class ParallelException extends RuntimeException
{
    public static function atLeastOneTask(): self
    {
        return new self(
            'Expected at least 1 task to run in parallel!'
        );
    }

    public static function invalidTask(string $task): self
    {
        return new self(
            sprintf('Script "%s" is not defined in this package', $task)
        );
    }

    public static function noResultForTaskYet(string $task): self
    {
        return new self(
            sprintf('Received no result for task %s yet.', $task)
        );
    }

    public static function noProcessExecutorDetected(): self
    {
        return new self(
            'The composer Loop does not contain a ProcessExecutor. ' .
            'Please log an issue with detailed information on how to reproduce!'
        );
    }
}
