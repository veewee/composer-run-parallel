<?php

declare(strict_types=1);

namespace ComposerRunParallel\Finder;

use Composer\Util\ProcessExecutor;

class PhpExecutableFinder
{
    public function __invoke(): string
    {
        $finder = new \Symfony\Component\Process\PhpExecutableFinder();
        $phpPath = $finder->find(false);
        if (!$phpPath) {
            throw new \RuntimeException('Failed to locate PHP binary to execute '.$phpPath);
        }

        $phpArgs = $finder->findArguments();
        $phpArgs = $phpArgs ? ' ' . implode(' ', $phpArgs) : '';
        $allowUrlFOpenFlag = ' -d allow_url_fopen=' . ProcessExecutor::escape(ini_get('allow_url_fopen'));
        $disableFunctionsFlag = ' -d disable_functions=' . ProcessExecutor::escape(ini_get('disable_functions'));
        $memoryLimitFlag = ' -d memory_limit=' . ProcessExecutor::escape(ini_get('memory_limit'));

        return ProcessExecutor::escape($phpPath) . $phpArgs . $allowUrlFOpenFlag . $disableFunctionsFlag . $memoryLimitFlag;
    }
}
