<?php

declare(strict_types=1);

namespace ComposerRunParallel\Executor;

use Composer\Util\Loop;
use Composer\Util\ProcessExecutor;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use React\Promise\PromiseInterface;

final class AsyncTaskExecutor
{
    private Loop $loop;
    private PhpExecutableFinder $phpExecutableFinder;

    public function __construct(Loop $loop, PhpExecutableFinder $phpExecutableFinder)
    {
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->loop = $loop;
    }

    public function __invoke(string $task, array $args): PromiseInterface
    {
        if (!$executor = $this->loop->getProcessExecutor()) {
            throw ParallelException::noProcessExecutorDetected();
        }

        $process = $this->buildProcess($task, $args);

        return $executor->executeAsync($process);
    }

    private function buildProcess(string $task, array $args): string
    {
        return implode(' ', [
            ($this->phpExecutableFinder)(),
            ...array_map(
                static fn (string $argument): string => ProcessExecutor::escape($argument),
                [
                    getenv('COMPOSER_BINARY'),
                    'run',
                    $task,
                    ...$args
                ]
            )
        ]);
    }
}
