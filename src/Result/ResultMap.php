<?php

declare(strict_types=1);

namespace ComposerRunParallel\Result;

use ComposerRunParallel\Exception\ParallelException;

final class ResultMap
{
    /**
     * @var array<string, int>
     */
    private $map = [];

    public static function empty(): self
    {
        return new self();
    }

    public function registerResult(string $task, int $result): void
    {
        $this->map[$task] = $result;
    }

    /**
     * @throws ParallelException
     */
    public function resultFor(string $task): int
    {
        if (!array_key_exists($task, $this->map)) {
            throw ParallelException::noResultForTaskYet($task);
        }

        return $this->map[$task];
    }

    /**
     * @throws ParallelException
     *
     * @return list<string>
     */
    public function listFailedTasks(): array
    {
        return array_reduce(
            array_keys($this->map),
            /**
             * @param list<string> $failed
             *
             * @return list<string>
             */
            fn (array $failed, string $task): array => $this->resultFor($task) > 0 ? [...$failed, $task] : $failed,
            []
        );
    }

    /**
     *     @throws ParallelException
     *
     * @return list<string>
     */
    public function listSucceededTasks(): array
    {
        return array_reduce(
            array_keys($this->map),
            /**
             * @param list<string> $succeeded
             *
             * @return list<string>
             */
            fn (array $succeeded, string $task): array => 0 === $this->resultFor($task) ? [...$succeeded, $task] : $succeeded,
            []
        );
    }

    public function getResultCode(): int
    {
        return array_reduce(
            $this->map,
            fn (int $highest, int $value): int => (int) max($highest, $value),
            0
        );
    }

    /**
     * @param callable(int): int $onSuccess
     * @param callable(int): int $onFailure
     */
    public function conclude(
        callable $onSuccess,
        callable $onFailure
    ): int {
        $resultCode = $this->getResultCode();

        return 0 === $resultCode ? $onSuccess($resultCode) : $onFailure($resultCode);
    }
}
