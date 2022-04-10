<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Tools;

use Test\Cli\CliArgParser;
use Test\Enum\TestStyle;

/**
 * Factory for object representation of difference checking program
 */
class DiffProgramFactory
{

    /**
     * Creates instance compatible with DiffProgram interface
     *
     * @param CliArgParser $cliArgParser CLI argument parser (dependency)
     * @param TmpManager $tmpManager Manager of the temporary directory (dependency)
     *
     * @return DiffProgram Object with interface of DiffProgram
     */
    public static function createDiffProgram(CliArgParser $cliArgParser, TmpManager $tmpManager): DiffProgram
    {
        if($cliArgParser->getTestStyle() == TestStyle::PARSE) {
            return new JExamXmlDiff($cliArgParser->getJExamPath(), $tmpManager->getTmpDir());
        }

        return new UnixDiff;
    }
}
