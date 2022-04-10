<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

use Test\Cli\CliArgParser;
use Test\Enum\ExitCode;
use Test\Exceptions\BadNumberOfInputArgsException;
use Test\Exceptions\InternalErrorException;
use Test\Exceptions\InvalidDirOrFileArgException;
use Test\Exceptions\InvalidInputArgValueException;
use Test\Testing\TesterFactory;
use Test\Tools\DiffProgramFactory;
use Test\Tools\TmpManager;

ini_set('display_errors', 'stderr');

spl_autoload_register(function(string $fullyQualifiedClassName) {
    $withoutPrefix = str_replace('Test\\', '', $fullyQualifiedClassName);
    $asPath = str_replace('\\', '/', $withoutPrefix);

    /** @noinspection PhpIncludeInspection Generated path */
    require_once __DIR__ . "/test/$asPath.php";
});

// Process CLI input arguments
// Warning: for using $argc and $argv register_argc_argv must be enabled
try {
    $cliArgParser = new CliArgParser($argc, $argv);
} catch(BadNumberOfInputArgsException|InvalidInputArgValueException $e) {
    exit(ExitCode::WRONG_INPUT_ARGS->value);
} catch(InvalidDirOrFileArgException $e) {
    exit(ExitCode::INVALID_DIR_FILE_ARG->value);
}

// Needed objects
try {
    $tmpManager = TmpManager::getInstance(!$cliArgParser->isNoClean());
} catch(InternalErrorException $e) {
    exit(ExitCode::INTERNAL_ERROR->value);
}

$diffProgram = DiffProgramFactory::createDiffProgram($cliArgParser, $tmpManager);
$tester = TesterFactory::createTester($cliArgParser, $diffProgram, $tmpManager);

// Start processing
$testSuite = $tester->createTestSuite();
$testReport = $tester->test($testSuite);
$testReport->setEnd();

var_dump($testReport);
var_dump($testReport->countTestingLength());
var_dump($testReport->countSuccessful());
var_dump($testReport->countFailed());
