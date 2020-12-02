<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\NullIO;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use ComposerRunParallel\Command\ParallelCommandsProvider;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\ParallelPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerRunParallel\ParallelPlugin
 * @covers \ComposerRunParallel\Scripts\ParallelScript
 */
final class ParallelPluginTest extends TestCase
{
    /** @test */
    public function it_is_a_composer_plugin(): void
    {
        $plugin = new ParallelPlugin();
        $io = new NullIO();
        $composer = new Composer();

        self::assertInstanceOf(PluginInterface::class, $plugin);

        // These should do nothing for now ...
        $plugin->activate($composer, $io);
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }

    /** @test */
    public function it_is_a_composer_event_subscriber(): void
    {
        $plugin = new ParallelPlugin();
        self::assertInstanceOf(EventSubscriberInterface::class, $plugin);

        self::assertSame(['parallel' => 'runParallelScript'], ParallelPlugin::getSubscribedEvents());
    }

    /** @test */
    public function it_registers_commands(): void
    {
        $plugin = new ParallelPlugin();
        self::assertInstanceOf(Capable::class, $plugin);

        self::assertSame(
            [
                CommandProvider::class => ParallelCommandsProvider::class,
            ],
            $plugin->getCapabilities()
        );
    }

    /** @test */
    public function it_can_run_the_parallel_script(): void
    {
        $composer = new Composer();
        $io = new NullIO();

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage(ParallelException::atLeastOneTask()->getMessage());

        $plugin = new ParallelPlugin();
        $plugin->runParallelScript(new Event('parallel', $composer, $io));
    }
}
