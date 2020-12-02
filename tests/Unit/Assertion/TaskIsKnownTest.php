<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Assertion;

use Composer\Composer;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\NullIO;
use Composer\Script\Event;
use ComposerRunParallel\Assertion\TaskIsKnown;
use ComposerRunParallel\Exception\ParallelException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerRunParallel\Assertion\TaskIsKnown
 */
final class TaskIsKnownTest extends TestCase
{
    private Event $event;
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->dispatcher
            ->method('hasEventListeners')
            ->will($this->returnCallback(
                static fn (Event $event) => in_array($event->getName(), ['task1', 'task2'], true)
            ));

        $composer = new Composer();
        $composer->setEventDispatcher($this->dispatcher);
        $io = new NullIO();

        $this->event = new Event('parallel', $composer, $io);
    }

    /** @test */
    public function it_knows_if_a_task_is_registered(): void
    {
        $this->expectNotToPerformAssertions();

        TaskIsKnown::one($this->event, 'task1');
        TaskIsKnown::one($this->event, 'task2');
    }

    /** @test */
    public function it_knows_if_a_task_is_not_registered(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::invalidTask('task3')->getMessage());

        TaskIsKnown::one($this->event, 'task3');
    }

    /** @test */
    public function it_knows_if_all_tasks_are_registered(): void
    {
        $this->expectNotToPerformAssertions();

        TaskIsKnown::all($this->event, ['task1', 'task2']);
    }

    /** @test */
    public function it_knows_if_one_of_all_tasks_is_not_registered(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::invalidTask('task3')->getMessage());

        TaskIsKnown::all($this->event, ['task1', 'task2', 'task3']);
    }
}
