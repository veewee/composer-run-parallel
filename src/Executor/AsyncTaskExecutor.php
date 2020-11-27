<?php

declare(strict_types=1);

namespace ComposerRunParallel\Executor;

use Composer\Util\Loop;
use Composer\Util\ProcessExecutor;
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
        $executor = $this->loop->getProcessExecutor();
        $process = $this->buildProcess($task, $args);

        return $executor->executeAsync($process);
    }

    private function buildProcess(string $task, array $args): string
    {
        return sprintf(
            '%s %s run %s %s',
            ($this->phpExecutableFinder)(),
            ProcessExecutor::escape(getenv('COMPOSER_BINARY')),
            ProcessExecutor::escape($task),
            implode(' ', $args)
        );
    }
}
