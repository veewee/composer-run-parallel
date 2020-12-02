<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Executor;

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
}
