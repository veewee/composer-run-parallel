<?php

declare(strict_types=1);

namespace ComposerRunParallel;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use ComposerRunParallel\Scripts\ParallelScript;

final class ParallelPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            'parallel' => 'runParallelScript',
        ];
    }

    public function runParallelScript(Event $event): int
    {
        return (new ParallelScript())($event);
    }
}
