<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Executor;

use Composer\Util\Loop;
use Composer\Util\ProcessExecutor;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Executor\AsyncTaskExecutor;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\FulfilledPromise;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;

/**
 * @covers \ComposerRunParallel\Executor\AsyncTaskExecutor
 * @covers \ComposerRunParallel\Finder\PhpExecutableFinder
 */
final class AsyncTaskExecutorTest extends TestCase
{
    private AsyncTaskExecutor $asyncTaskExecutor;

    /** @var Loop&MockObject */
    private MockObject $loop;

    /** @var MockObject&ProcessExecutor */
    private MockObject $processExecutor;

    protected function setUp(): void
    {
        $this->loop = $this->createMock(Loop::class);
        $this->processExecutor = $this->createMock(ProcessExecutor::class);

        $executableFinderMock = $this->createMock(SymfonyPhpExecutableFinder::class);
        $executableFinderMock->method('find')->willReturn('php');
        $executableFinderMock->method('findArguments')->willReturn(['phparg']);

        $this->asyncTaskExecutor = new AsyncTaskExecutor($this->loop, new PhpExecutableFinder($executableFinderMock));
    }

    /** @test */
    public function it_throws_exception_without_executor(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::noProcessExecutorDetected()->getMessage());

        ($this->asyncTaskExecutor)('task', []);
    }

    /** @test */
    public function it_can_execute_a_task_async(): void
    {
        $this->loop->method('getProcessExecutor')->willReturn($this->processExecutor);
        $this->processExecutor
            ->method('executeAsync')
            ->with($this->buildExecutableString()." 'task' 'arg1' 'arg2'")
            ->willReturn($expected = new FulfilledPromise('yes'));

        $result = ($this->asyncTaskExecutor)('task', ['arg1', 'arg2']);
        self::assertSame($expected, $result);
    }

    private function buildExecutableString()
    {
        return implode(' ', [
            ProcessExecutor::escape('php'),
            'phparg',
            '-d allow_url_fopen='.ProcessExecutor::escape(ini_get('allow_url_fopen')),
            '-d disable_functions='.ProcessExecutor::escape(ini_get('disable_functions')),
            '-d memory_limit='.ProcessExecutor::escape(ini_get('memory_limit')),
            ProcessExecutor::escape(getenv('COMPOSER_BINARY') ?: 'composer'),
            ProcessExecutor::escape('run'),
        ]);
    }
}
