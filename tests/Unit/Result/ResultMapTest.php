<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Result;

use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Result\ResultMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerRunParallel\Result\ResultMap
 */
final class ResultMapTest extends TestCase
{
    /** @test */
    public function it_can_handle_empty_result_map(): void
    {
        $result = ResultMap::empty();
        self::assertSame(0, $result->getResultCode());
        self::assertSame([], $result->listSucceededTasks());
        self::assertSame([], $result->listFailedTasks());
        self::assertSame('success', $result->conclude(
            static fn () => 'success',
            static fn () => 'failed'
        ));
    }

    /** @test */
    public function it_can_fetch_the_result_of_a_known_task(): void
    {
        $result = ResultMap::empty();
        $result->registerResult('task', 0);

        self::assertSame(0, $result->resultFor('task'));
    }

    /** @test */
    public function it_can_not_fetch_the_result_of_an_unknown_task(): void
    {
        $result = ResultMap::empty();
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::noResultForTaskYet('task')->getMessage());

        $result->resultFor('task');
    }

    /** @test */
    public function it_can_handle_all_succeeded_results(): void
    {
        $result = ResultMap::empty();
        $result->registerResult('task1', 0);
        $result->registerResult('task2', 0);

        self::assertSame(0, $result->getResultCode());
        self::assertSame(['task1', 'task2'], $result->listSucceededTasks());
        self::assertSame([], $result->listFailedTasks());
        self::assertSame('success', $result->conclude(
            static fn () => 'success',
            static fn () => 'failed'
        ));
    }

    /** @test */
    public function it_can_handle_all_failed_results(): void
    {
        $result = ResultMap::empty();
        $result->registerResult('task1', 1);
        $result->registerResult('task2', 2);

        self::assertSame(2, $result->getResultCode());
        self::assertSame([], $result->listSucceededTasks());
        self::assertSame(['task1', 'task2'], $result->listFailedTasks());
        self::assertSame('failed', $result->conclude(
            static fn () => 'success',
            static fn () => 'failed'
        ));
    }

    /** @test */
    public function it_can_handle_mixed_results(): void
    {
        $result = ResultMap::empty();
        $result->registerResult('task1', 0);
        $result->registerResult('task2', 2);

        self::assertSame(2, $result->getResultCode());
        self::assertSame(['task1'], $result->listSucceededTasks());
        self::assertSame(['task2'], $result->listFailedTasks());
        self::assertSame('failed', $result->conclude(
            static fn () => 'success',
            static fn () => 'failed'
        ));
    }
}
