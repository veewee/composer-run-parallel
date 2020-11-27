<?php

declare(strict_types=1);

namespace ComposerRunParallel\Scripts;

use Composer\Script\Event;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Executor\AsyncTaskExecutor;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use function React\Promise\all;

class ParallelScript
{
    public static function run(Event $event): void
    {
        $tasks = $event->getArguments();
        if (!$tasks) {
            throw ParallelException::atLeastOneTask();
        }

        self::assertAllTasksAreKnown($event, $tasks);

        $loop = $event->getComposer()->getLoop();
        $io = $event->getIO();
        $executor = new AsyncTaskExecutor($loop, new PhpExecutableFinder());

        $loop->wait(
            array_map(
                static fn (string $task) => $executor($task, [])
                    ->then(
                        static function (Process $process) use ($task, $io) {
                            $io->writeError($process->getErrorOutput());
                            $io->write($process->getOutput());

                            if (!$process->isSuccessful()) {
                                throw new \RuntimeException('Task ' .$task .' failed.');
                            }
                        }
                    ),
                $tasks
            )
        );
    }

    private static function assertAllTasksAreKnown(Event $event, array $tasks): void
    {
        $dispatcher = $event->getComposer()->getEventDispatcher();

        foreach ($tasks as $task) {
            if (!$dispatcher->hasEventListeners(self::cloneEventForTask($event, $task))) {
                throw ParallelException::invalidTask($task);
            }
        }
    }

    /**
     * TODO : find out if we can set arguments from the parallel task somehow ...
     */
    private static function cloneEventForTask(Event $event, string $task): Event
    {
        return new Event($task, $event->getComposer(), $event->getIO(), $event->isDevMode(), [], $event->getFlags());
    }
}
