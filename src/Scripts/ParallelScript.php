<?php

declare(strict_types=1);

namespace ComposerRunParallel\Scripts;

use Composer\EventDispatcher\ScriptExecutionException;
use Composer\Script\Event;
use ComposerRunParallel\Assertion;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Executor\AsyncTaskExecutor;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use ComposerRunParallel\Result\ResultMap;
use Symfony\Component\Process\Process;

class ParallelScript
{
    public static function initializeAndRun(Event $event): int
    {
        $instance = new self();

        return $instance($event);
    }

    public function __invoke(Event $event): int
    {
        $tasks = $event->getArguments();
        if (!$tasks) {
            throw ParallelException::atLeastOneTask();
        }
        Assertion\TaskIsKnown::all($event, $tasks);

        $loop = $event->getComposer()->getLoop();
        $io = $event->getIO();
        $executor = new AsyncTaskExecutor($loop, PhpExecutableFinder::default());
        $resultMap = ResultMap::empty();

        $io->write(['<warning>Running tasks in parallel:', ...$tasks, '</warning>']);

        $loop->wait(
            array_map(
                static fn (string $task) => $executor($task, [])
                    ->then(
                        static function (Process $process) use ($task, $io, $resultMap) {
                            $resultMap->registerResult($task, $process->getExitCode());

                            $io->write('<info>Finished task '.$task.'</info>');
                            $io->writeError($process->getErrorOutput());
                            $io->write([$process->getOutput(), '']);
                        }
                    ),
                $tasks
            )
        );

        return $resultMap->conclude(
            static function () use ($resultMap, $io) : int {
                $io->write(['<info>Finished running', ...$resultMap->listSucceededTasks(), '</info>']);

                return 0;
            },
            static function (int $resultCode) use ($io, $resultMap) : int {
                $succeeded = $resultMap->listSucceededTasks();
                if ($succeeded) {
                    $io->write(['<warning>Succesfully ran: ', ...$resultMap->listSucceededTasks(), '</warning>']);
                }

                $io->writeError([
                    '<error>Failed running: ',
                    ...$resultMap->listFailedTasks(),
                    '',
                    'Not all parallel tasks were executed in parallel!',
                    '</error>'
                ]);

                throw new ScriptExecutionException('Not all parallel tasks were executed in parallel', $resultCode);
            }
        );
    }
}
