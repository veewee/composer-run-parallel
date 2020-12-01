<?php

declare(strict_types=1);

namespace ComposerRunParallel\Test\Unit;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\PluginInterface;
use ComposerRunParallel\ParallelPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers ComposerRunParallel\ParallelPlugin
 */
final class ParallelPluginTest extends TestCase
{
    /** @test */
    public function it_is_a_composer_plugin(): void
    {
        self::assertInstanceOf(PluginInterface::class, new ParallelPlugin());
    }

    /** @test */
    public function it_is_a_composer_event_subscriber(): void
    {
        $plugin = new ParallelPlugin();
        self::assertInstanceOf(EventSubscriberInterface::class, $plugin);

        self::assertSame(['parallel' => 'runParallelScript'], ParallelPlugin::getSubscribedEvents());
    }

    /** @test */
    public function it_can_run_the_parallel_script(): void
    {

    }
}
