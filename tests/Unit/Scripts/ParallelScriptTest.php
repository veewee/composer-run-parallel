<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Script;

use Composer\Composer;
use Composer\EventDispatcher\EventDispatcher;
use Composer\EventDispatcher\ScriptExecutionException;
use Composer\IO\BufferIO;
use Composer\Script\Event;
use Composer\Util\HttpDownloader;
use Composer\Util\Loop;
use Composer\Util\ProcessExecutor;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use ComposerRunParallel\Scripts\ParallelScript;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\FulfilledPromise;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @covers \ComposerRunParallel\Scripts\ParallelScript
 */
class ParallelScriptTest extends TestCase
{
    private BufferIO $io;
    private Composer $composer;
    private ParallelScript $script;

    /** @var MockObject & ProcessExecutor */
    private MockObject $processExecutor;

    protected function setUp(): void
    {
        $httpDownloader = $this->createMock(HttpDownloader::class);
        $this->processExecutor = $this->createMock(ProcessExecutor::class);
        $finder = $this->createMock(SymfonyPhpExecutableFinder::class);
        $finder->method('find')->willReturn('php');
        $finder->method('findArguments')->willReturn([]);

        $this->io = new BufferIO();

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher
            ->method('hasEventListeners')
            ->will($this->returnCallback(
                static fn (Event $event) => in_array($event->getName(), ['task1', 'task2'], true)
            ));

        $this->composer = new Composer();
        $this->composer->setEventDispatcher($dispatcher);
        $this->composer->setLoop(new Loop($httpDownloader, $this->processExecutor));

        $this->script = new ParallelScript(
            new PhpExecutableFinder($finder)
        );
    }


    /** @test */
    public function it_fails_if_there_are_not_tasks_specified(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::atLeastOneTask()->getMessage());

        ($this->script)($this->createEvent([]));
    }

    /** @test */
    public function it_fails_if_a_task_is_not_known(): void
    {
        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::invalidTask('unkown')->getMessage());

        ($this->script)($this->createEvent(['task1', 'unkown']));
    }

    /** @test */
    public function it_can_successfully_run_scripts_in_parallel(): void
    {
        $this->processExecutor->method('executeAsync')->willReturn(
            $this->createProcessResult(true)
        );

        $result = ($this->script)($this->createEvent(['task1']));

        self::assertEquals(0, $result);

        $output = $this->io->getOutput();
        self::assertStringContainsString('Finished running: ' . PHP_EOL . 'task1', $output);
    }

    /** @test */
    public function it_can_insuccessfully_run_scripts_in_parallel(): void
    {
        $this->processExecutor->method('executeAsync')->willReturn(
            $this->createProcessResult(false),
            $this->createProcessResult(true)
        );
        $exception = null;

        try {
            ($this->script)($this->createEvent(['task1', 'task2']));
        } catch (ScriptExecutionException $exception) {

        }

        self::assertInstanceOf(ScriptExecutionException::class, $exception);

        $output = $this->io->getOutput();
        self::assertStringContainsString('Succesfully ran: ' . PHP_EOL . 'task2', $output);
        self::assertStringContainsString('Failed running: ' . PHP_EOL . 'task1', $output);
        self::assertStringContainsString('Not all tasks could be executed successfully!', $output);
    }

    private function createProcessResult(bool $success): FulfilledPromise
    {
        $process = $this->createMock(Process::class);
        $process->method('getExitCode')->willReturn($success ? 0 : 1);
        $process->method('getOutput')->willReturn('stdout');
        $process->method('getErrorOutput')->willReturn('stderr');

        return new FulfilledPromise($process);
    }

    private function createEvent(array $tasks): Event
    {
        return new Event('parallel', $this->composer, $this->io, false, $tasks);
    }
}
