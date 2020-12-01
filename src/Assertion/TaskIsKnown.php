<?php

declare(strict_types=1);

namespace ComposerRunParallel\Assertion;

use Composer\Script\Event;
use ComposerRunParallel\Exception\ParallelException;

final class TaskIsKnown
{
    /**
     * @throws ParallelException
     */
    public static function one(Event $event, string $task): void
    {
        $dispatcher = $event->getComposer()->getEventDispatcher();
        if (!$dispatcher->hasEventListeners(self::cloneEventForTask($event, $task))) {
            throw ParallelException::invalidTask($task);
        }
    }

    /**
     * @param list<string> $tasks
     *
     * @throws ParallelException
     */
    public static function all(Event $event, array $tasks): void
    {
        foreach ($tasks as $task) {
            self::one($event, $task);
        }
    }

    private static function cloneEventForTask(Event $event, string $task): Event
    {
        return new Event($task, $event->getComposer(), $event->getIO(), $event->isDevMode(), [], $event->getFlags());
    }
}
