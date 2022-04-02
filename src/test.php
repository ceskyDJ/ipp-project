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
use Test\Exceptions\InvalidDirectoryException;
use Test\Exceptions\InvalidInputArgValueException;

ini_set('display_errors', 'stderr');

spl_autoload_register(function(string $fullyQualifiedClassName) {
    $withoutPrefix = str_replace('Test\\', '', $fullyQualifiedClassName);
    $asPath = str_replace('\\', '/', $withoutPrefix);

    /** @noinspection PhpIncludeInspection Generated path */
    require_once __DIR__ . "/test/$asPath.php";
});

// Warning: for using $argc and $argv register_argc_argv must be enabled
try {
    $cliArgParser = new CliArgParser($argc, $argv);
}
catch(BadNumberOfInputArgsException|InvalidInputArgValueException|InvalidDirectoryException $e) {
    exit(ExitCode::WRONG_INPUT_ARGS->value);
}

// Needed objects

// Start processing
