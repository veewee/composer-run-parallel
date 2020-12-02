<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit\Command;

use Composer\Plugin\Capability\CommandProvider;
use ComposerRunParallel\Command\ParallelCommandsProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerRunParallel\Command\ParallelCommandsProvider
 */
final class ParallelCommandsProviderTest extends TestCase
{
    /** @test */
    public function it_can_provide_commands(): void
    {
        $provider = new ParallelCommandsProvider();

        self::assertInstanceOf(CommandProvider::class, $provider);

        $commands = $provider->getCommands();
        self::assertCount(1, $commands);
        self::assertSame('parallel', $commands[0]->getName());
    }
}
