<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

use Parse\Cli\CliArgParser;
use Parse\Enum\ExitCode;
use Parse\Exceptions\BadNumberOfInputArgsException;
use Parse\Exceptions\InvalidHeaderException;
use Parse\Exceptions\InvalidInputArgValueException;
use Parse\Exceptions\InvalidOpCodeException;
use Parse\Exceptions\LexicalErrorException;
use Parse\Exceptions\SyntaxErrorException;
use Parse\Translation\Generator;
use Parse\Translation\Parser;
use Parse\Translation\Scanner;

ini_set('display_errors', 'stderr');

spl_autoload_register(function(string $fullyQualifiedClassName) {
    $withoutPrefix = str_replace('Parse\\', '', $fullyQualifiedClassName);
    $asPath = str_replace('\\', '/', $withoutPrefix);

    /** @noinspection PhpIncludeInspection Generated path */
    require_once __DIR__ . "/parse/$asPath.php";
});

// Warning: for using $argc and $argv register_argc_argv must be enabled
try {
    $cliArgParser = new CliArgParser($argc, $argv);
}
catch(BadNumberOfInputArgsException|InvalidInputArgValueException $e) {
    exit(ExitCode::WRONG_INPUT_ARGS->value);
}

// Needed objects
$scanner = new Scanner;
$generator = new Generator;
$parser = new Parser($scanner, $generator);

// Start processing
try {
    $parser->parse();
} catch(InvalidHeaderException $e) {
    exit(ExitCode::INVALID_HEADER->value);
} catch(InvalidOpCodeException $e) {
    exit(ExitCode::INVALID_OPCODE->value);
} catch(LexicalErrorException|SyntaxErrorException $e) {
    exit(ExitCode::OTHER_LEX_SYNTAX_ERROR->value);
}

echo $generator->writeXml();
