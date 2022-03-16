<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Cli;

use App\Enum\ExitCode;
use App\Exceptions\BadNumberOfInputArgsException;
use App\Exceptions\InvalidInputArgValueException;

/**
 * Parser of input from command line interface
 */
class CliArgParser
{

    /**
     * @var int Number of CLI arguments
     */
    private int $argc;
    /**
     * @var array CLI arguments
     */
    private array $argv;
    /**
     * @var array Input arguments parsed by methods of this class
     */
    private array $parsedArgs;

    /**
     * Class constructor
     *
     * @param int $argc Number of CLI arguments (in raw form, from $argc PHP variable)
     * @param array $argv CLI arguments (in raw form, from $argv PHP variable)
     *
     * @throws BadNumberOfInputArgsException Too many input arguments given
     * @throws InvalidInputArgValueException
     */
    public function __construct(int $argc, array $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;

        $this->parseCliArgs();

        if(key_exists("help", $this->parsedArgs)) {
            $this->writeHelp();
        }
    }

    /**
     * Parses input arguments from CLI
     *
     * @return void
     * @throws BadNumberOfInputArgsException Too many input args given
     * @throws InvalidInputArgValueException
     */
    private function parseCliArgs(): void
    {
        $shortSwitches = '';
        $longSwitches = ['help::']; // --help has optional value (::) only for input validation
        $usedInputArgs = 0;

        $this->parsedArgs = getopt($shortSwitches, $longSwitches, $usedInputArgs);

        // --help must be used alone
        if(key_exists('help', $this->parsedArgs) && $this->argc > 2) {
            throw new BadNumberOfInputArgsException("You can't use any other input argument alongside --help");
        }

        if(isset($this->parsedArgs['help']) && $this->parsedArgs['help'] != false) {
            throw new InvalidInputArgValueException("--help switch can't have any value");
        }

        // All arguments must be used
        if($usedInputArgs != $this->argc) {
            $unknownArgs = implode(" ", array_slice($this->argv, $usedInputArgs));
            throw new BadNumberOfInputArgsException("Can't process unknown input arguments: $unknownArgs");
        }
    }

    /**
     * Writes a help message
     *
     * @return never After writing the help ends with exit code 0
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection Just solution for a bug in PHPStorm
     */
    private function writeHelp(): never
    {
        $successExitCode = ExitCode::SUCCESS->value;
        $wrongArgsExitCode = ExitCode::WRONG_INPUT_ARGS->value;

        echo <<<EOF
        parse.php je skript pro zpracovani jazyka IPPcode22 a jeho prevod do XML reprezentace. Tu je dale mozne
        pouzit jako vstup pro skript interpret.py, ktery provadi interpretaci zdrojoveho kodu. Jedna se
        o soucast 1. casti projektu do predmetu IPP na FIT VUT.
        
        Pouziti:
        php8.1 parse.php [--help]
        
        Nepovinne parametry:
          --help                    Zobrazi tuto napovedu a skonci s navratovym kodem $successExitCode. Tento
                                    parametr nemuze byt kombinovan s jinymi parametry. V opacnem pripade
                                    dochazi k chybe a skript je ukoncen s navratovym kodem $wrongArgsExitCode.
        EOF;

        exit(ExitCode::SUCCESS->value);
    }

    /**
     * Returns a value of the argument
     *
     * @param string $argName Name of the argument
     *
     * @return string|null Argument's value of null if argument isn't given
     */
    public function getArgValue(string $argName): ?string
    {
        return $this->parsedArgs[$argName] ?? null;
    }
}
