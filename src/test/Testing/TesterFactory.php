<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Testing;

use Test\Cli\CliArgParser;
use Test\Enum\TestStyle;
use Test\Exceptions\UnsupportedTestStyleError;
use Test\Tools\DiffProgram;
use Test\Tools\TmpManager;

/**
 * Factory for test manager (child of Tester)
 */
class TesterFactory
{

    /**
     * Creates a test manager with settings get from the dependency objects
     *
     * @param CliArgParser $cliArgParser CLI argument parser (dependency)
     * @param DiffProgram $diffProgram Diff program to pass to the tester (dependency)
     * @param TmpManager $tmpManager Temporary folder manager (dependency)
     *
     * @return Tester Created child of Tester
     */
    public static function createTester(
        CliArgParser $cliArgParser,
        DiffProgram $diffProgram,
        TmpManager $tmpManager
    ): Tester {
        return match ($cliArgParser->getTestStyle()) {
            TestStyle::PARSE => new ParseTester(
                $diffProgram,
                $cliArgParser->isRecursive(),
                $cliArgParser->getDirectory(),
                $tmpManager->getTmpDir(),
                $cliArgParser->getParseScript(),
                $cliArgParser->getIntScript()
            ),
            TestStyle::INTERPRETER => new InterpreterTester(
                $diffProgram,
                $cliArgParser->isRecursive(),
                $cliArgParser->getDirectory(),
                $tmpManager->getTmpDir(),
                $cliArgParser->getParseScript(),
                $cliArgParser->getIntScript()
            ),
            TestStyle::BOTH => new BothTester(
                $diffProgram,
                $cliArgParser->isRecursive(),
                $cliArgParser->getDirectory(),
                $tmpManager->getTmpDir(),
                $cliArgParser->getParseScript(),
                $cliArgParser->getIntScript()
            ),
            default => throw new UnsupportedTestStyleError('Trying to use unsupported test style'),
        };
    }
}
