<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

use App\Entity\Argument;
use App\Entity\Instruction;
use App\Enum\ArgType;
use App\Enum\OpCode;
use App\Translation\Generator;

ini_set('display_errors', 'stderr');

spl_autoload_register(function(string $fullyQualifiedClassName) {
    $withoutPrefix = str_replace('App\\', '', $fullyQualifiedClassName);
    $asPath = str_replace('\\', '/', $withoutPrefix);

    /** @noinspection PhpIncludeInspection Generated path */
    require_once __DIR__ . "/php/{$asPath}.php";
});

// TODO: for testing purposes only, remove after testing
$arg1 = new Argument(ArgType::VAR, "LF@hello");
$arg2 = new Argument(ArgType::STRING, "Hello\\032world!");

$instruction = new Instruction(OpCode::MOVE);
$instruction->addArgument($arg1);
$instruction->addArgument($arg2);

$outputManager = new Generator;
$outputManager->addInstruction($instruction);

echo $outputManager->writeXml();
