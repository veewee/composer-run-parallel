<?php

declare(strict_types=1);

namespace ComposerRunParallel\Finder;

use Composer\Util\ProcessExecutor;
use ComposerRunParallel\Exception\ParallelException;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;

final class PhpExecutableFinder
{
    private SymfonyPhpExecutableFinder $finder;

    public function __construct(SymfonyPhpExecutableFinder $finder)
    {
        $this->finder = $finder;
    }

    public static function default(): self
    {
        return new self(new SymfonyPhpExecutableFinder());
    }

    /**
     * Borrowed from Composer internals:.
     *
     * @see \Composer\EventDispatcher\EventDispatcher::getPhpExecCommand()
     *
     * @throws ParallelException
     */
    public function __invoke(): string
    {
        $phpPath = $this->finder->find(false);
        if (!$phpPath) {
            throw ParallelException::phpBinaryNotFound();
        }

        /** @var list<string> $phpArgs */
        $phpArgs = $this->finder->findArguments();
        $phpArgs = $phpArgs ? ' '.implode(' ', $phpArgs) : '';
        $allowUrlFOpenFlag = ' -d allow_url_fopen='.ProcessExecutor::escape(ini_get('allow_url_fopen'));
        $disableFunctionsFlag = ' -d disable_functions='.ProcessExecutor::escape(ini_get('disable_functions'));
        $memoryLimitFlag = ' -d memory_limit='.ProcessExecutor::escape(ini_get('memory_limit'));

        return ProcessExecutor::escape($phpPath).$phpArgs.$allowUrlFOpenFlag.$disableFunctionsFlag.$memoryLimitFlag;
    }
}
