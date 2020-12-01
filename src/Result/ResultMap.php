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

    public function resultFor(string $task): int
    {
        if (!array_key_exists($task, $this->map)) {
            throw ParallelException::noResultForTaskYet($task);
        }

        return $this->map[$task];
    }

    /**
     * @return list<string>
     */
    public function listFailedTasks(): array
    {
        return array_reduce(
            array_keys($this->map),
            fn (array $failed, string $task) => $this->resultFor($task) > 0 ? [...$failed, $task] : $failed,
            []
        );
    }

    /**
     * @return list<string>
     */
    public function listSucceededTasks(): array
    {
        return array_reduce(
            array_keys($this->map),
            fn (array $succeeded, string $task) => $this->resultFor($task) === 0 ? [...$succeeded, $task] : $succeeded,
            []
        );
    }

    public function getResultCode(): int
    {
        return array_reduce(
            $this->map,
            fn (int $highest, $value) => max($highest, $value),
            0
        );
    }

    /**
     * @param callable(int): int $onSuccess
     * @param callable(int): int $onFailure
     *
     * @return int
     */
    public function conclude(
        callable $onSuccess,
        callable $onFailure
    ): int
    {
        $resultCode = $this->getResultCode();

        return $resultCode === 0 ? $onSuccess($resultCode) : $onFailure($resultCode);
    }
}
