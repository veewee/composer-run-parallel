<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Exception;

use ComposerRunParallel\Exception\ParallelException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerRunParallel\Exception\ParallelException
 */
final class ParallelExceptionTest extends TestCase
{
    /** @test */
    public function it_can_throw_exception_on_invalid_amount_of_tasks(): void
    {
        $this->expectException(ParallelException::class);

        throw ParallelException::atLeastOneTask();
    }

    /** @test */
    public function it_can_throw_exception_on_unkown_task(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('taskName');

        throw ParallelException::invalidTask('taskName');
    }

    /** @test */
    public function it_can_throw_exception_on_no_result_yet_for_task(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('taskName');

        throw ParallelException::noResultForTaskYet('taskName');
    }

    /** @test */
    public function it_can_throw_exception_if_composer_does_not_contain_executor(): void
    {
        $this->expectException(ParallelException::class);

        throw ParallelException::noProcessExecutorDetected();
    }

    /** @test */
    public function it_can_throw_exception_if_php_binary_cannot_be_found(): void
    {
        $this->expectException(ParallelException::class);

        throw ParallelException::phpBinaryNotFound();
    }
}
