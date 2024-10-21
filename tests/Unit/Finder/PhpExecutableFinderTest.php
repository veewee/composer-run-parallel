<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Executor;

use Composer\Util\ProcessExecutor;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Finder\PhpExecutableFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;

/**
 * @covers \ComposerRunParallel\Finder\PhpExecutableFinder
 */
final class PhpExecutableFinderTest extends TestCase
{
    /** @test */
    public function it_throws_exception_on_php_executable_not_found(): void
    {
        $finder = $this->createMock(SymfonyPhpExecutableFinder::class);
        $finder->method('find')->willReturn(false);

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::phpBinaryNotFound()->getMessage());

        $phpExecutableFinder = new PhpExecutableFinder($finder);
        $phpExecutableFinder();
    }

    /** @test */
    public function it_can_find_executable(): void
    {
        $finder = $this->createMock(SymfonyPhpExecutableFinder::class);
        $finder->method('find')->with(false)->willReturn('php');

        $phpExecutableFinder = new PhpExecutableFinder($finder);
        $actual = $phpExecutableFinder();

        $allowUrlFOpenFlag = '-d allow_url_fopen='.ProcessExecutor::escape(ini_get('allow_url_fopen'));
        $disableFunctionsFlag = '-d disable_functions='.ProcessExecutor::escape(ini_get('disable_functions'));
        $memoryLimitFlag = '-d memory_limit='.ProcessExecutor::escape(ini_get('memory_limit'));

        self::assertSame(
            <<<EOCLI
            'php' {$allowUrlFOpenFlag} {$disableFunctionsFlag} {$memoryLimitFlag}
            EOCLI,
            $actual
        );
    }

    /** @test */
    public function it_can_create_a_default_finder(): void
    {
        $phpExecutableFinder = PhpExecutableFinder::default();
        self::assertInstanceOf(PhpExecutableFinder::class, $phpExecutableFinder);
    }
}
