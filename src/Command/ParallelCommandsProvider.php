<?php

declare(strict_types=1);

namespace ComposerRunParallel\Command;

use Composer\Command\ScriptAliasCommand;
use Composer\Plugin\Capability\CommandProvider;

final class ParallelCommandsProvider implements CommandProvider
{
    #[\Override]
    /**
     * @return list<ScriptAliasCommand>
     */
    public function getCommands(): array
    {
        return [
            new ScriptAliasCommand('parallel', 'Makes it possible to run composer scripts in parallel'),
        ];
    }
}
