<?php

declare(strict_types=1);

namespace ComposerRunParallel\Exception;

use RuntimeException;

class ParallelException extends RuntimeException
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
}
