<?php

declare(strict_types=1);

namespace ComposerRunParallel;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\EventDispatcher\ScriptExecutionException;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use ComposerRunParallel\Command\ParallelCommandsProvider;
use ComposerRunParallel\Exception\ParallelException;
use ComposerRunParallel\Scripts\ParallelScript;

/** @psalm-api */
final class ParallelPlugin implements PluginInterface, EventSubscriberInterface, Capable
{
    #[\Override]
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    #[\Override]
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    #[\Override]
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'parallel' => 'runParallelScript',
        ];
    }

    /**
     * @return array<class-string, class-string>
     */
    #[\Override]
    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => ParallelCommandsProvider::class,
        ];
    }

    /**
     * @throws ParallelException
     * @throws ScriptExecutionException
     */
    public function runParallelScript(Event $event): int
    {
        return ParallelScript::initializeAndRun($event);
    }
}
