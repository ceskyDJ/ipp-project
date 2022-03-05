<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

use App\Cli\CliArgParser;
use App\Enum\ExitCode;
use App\Exceptions\BadNumberOfInputArgsException;
use App\Exceptions\InvalidInputArgValueException;
use App\Translation\Generator;

ini_set('display_errors', 'stderr');

spl_autoload_register(function(string $fullyQualifiedClassName) {
    $withoutPrefix = str_replace('App\\', '', $fullyQualifiedClassName);
    $asPath = str_replace('\\', '/', $withoutPrefix);

    /** @noinspection PhpIncludeInspection Generated path */
    require_once __DIR__ . "/php/$asPath.php";
});

// Warning: for using $argc and $argv register_argc_argv must be enabled
try {
    $cliArgParser = new CliArgParser($argc, $argv);
}
catch(BadNumberOfInputArgsException|InvalidInputArgValueException $e) {
    exit(ExitCode::WRONG_INPUT_ARGS);
}

$outputManager = new Generator;

echo $outputManager->writeXml();
