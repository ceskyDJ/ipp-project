<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Cli;

use Test\Enum\ExitCode;
use Test\Exceptions\BadNumberOfInputArgsException;
use Test\Exceptions\InvalidDirectoryException;
use Test\Exceptions\InvalidInputArgValueException;

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
     * @throws InvalidInputArgValueException Missing required value of argument or excess value of switch
     * @throws InvalidDirectoryException Missing  directory or directory the script hasn't got access to
     */
    public function __construct(int $argc, array $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;

        $this->parseCliArgs();
        $this->setDefaults();

        if(key_exists("help", $this->parsedArgs)) {
            $this->writeHelp();
        }
    }

    /**
     * Parses input arguments from CLI
     *
     * @return void
     * @throws BadNumberOfInputArgsException Too many input args given
     * @throws InvalidInputArgValueException Missing required value of argument or excess value of switch
     * @throws InvalidDirectoryException Missing  directory or directory the script hasn't got access to
     */
    private function parseCliArgs(): void
    {
        $shortSwitches = '';
        // All arguments have optional value (::), it is for better input validation only
        $longSwitches = [
            'help::', 'directory::', 'recursive::', 'parse-script::', 'int-script::', 'parse-only::', 'int-only::',
            'jexampath::', 'noclean::'
        ];
        $usedInputArgs = 0;

        $this->parsedArgs = getopt($shortSwitches, $longSwitches, $usedInputArgs);

        // --help must be used alone
        if(key_exists('help', $this->parsedArgs) && $this->argc > 2) {
            throw new BadNumberOfInputArgsException("You can't use any other input argument alongside --help");
        }

        // --parse-only and --int-only can't be used together
        if(isset($this->parsedArgs['parse-only']) && isset($this->parsedArgs['int-only'])) {
            throw new BadNumberOfInputArgsException("--parse-only and --int-only can't be used together");
        }

        // No-value arguments (switches) can't have any value
        $noValueArguments = ['help', 'recursive', 'parse-only', 'int-only', 'noclean'];
        foreach($noValueArguments as $argument) {
            if(isset($this->parsedArgs[$argument]) && $this->parsedArgs[$argument] != false) {
                throw new InvalidInputArgValueException("--$argument switch can't have any value");
            }
        }

        // Value arguments must have a value
        $valueArguments = ['directory', 'parse-script', 'int-script', 'jexampath'];
        foreach($valueArguments as $argument) {
            if(isset($this->parsedArgs[$argument]) && $this->parsedArgs[$argument] == false) {
                throw new InvalidInputArgValueException("--$argument argument must have a value");
            }
        }

        // Directory arguments must have a valid directory
        $directoryArguments = ['directory', 'jexampath'];
        foreach($directoryArguments as $argument) {
            if(!isset($this->parsedArgs[$argument])) {
                continue;
            }

            $directory = $this->parsedArgs[$argument];
            if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
                throw new InvalidDirectoryException("Directory '$directory' in --$argument isn't valid.");
            }
        }

        // All arguments must be used
        if($usedInputArgs != $this->argc) {
            $unknownArgs = implode(" ", array_slice($this->argv, $usedInputArgs));
            throw new BadNumberOfInputArgsException("Can't process unknown input arguments: $unknownArgs");
        }
    }

    /**
     * Sets default values to unset input arguments
     *
     * @return void
     */
    private function setDefaults(): void
    {
        $this->parsedArgs['directory'] = $this->parsedArgs['directory'] ?? '.';
        $this->parsedArgs['parse-script'] = $this->parsedArgs['parse-script'] ?? 'parse.php';
        $this->parsedArgs['int-script'] = $this->parsedArgs['int-script'] ?? 'interpreter.py';
        $this->parsedArgs['jexampath'] = $this->parsedArgs['jexampath'] ?? '/pub/courses/ipp/jexamxml/';
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
        test.php je skript pro automaticke testovani skriptu parse.php a interpreter.py. Jsou vyuzivany
        predpripravene testy, ktere jsou pouze automaticky spousteny. Jedna se o soucast 2. casti projektu
        do predmetu IPP na FIT VUT.
        
        Pouziti:
        php8.1 test.php [--help] [--directory=path] [--recursive] [--parse-script=file] [--int-script=file]
            [--parse-only | --int-only] [--jexampath=path] [--noclean]
        
        Nepovinne parametry:
          --help                    Zobrazi tuto napovedu a skonci s navratovym kodem $successExitCode. Tento
                                    parametr nemuze byt kombinovan s jinymi parametry. V opacnem pripade
                                    dochazi k chybe a skript je ukoncen s navratovym kodem $wrongArgsExitCode.
          --directory=path          Testy budou hledany v zadanem adresari path. Bez pouziti tohoto argumentu
                                    budou testy hledany v aktualnim adresari.
          --recursive               Pri pouziti tohoto prepinace budou testy hledany nejen primo v danem
                                    adresari, ale take rekurzivne ve vsech jeho podadresarich.
          --parse-script=file       Cesta k souboru skriptu pro analyzuji zdrojoveho kodu v jazyce IPPcode22.
                                    Pokud neni tento argument zadan, predpoklada se soubor parse.php
                                    v aktualnim adresari.
          --int-script=file         Cesta k souboru skriptu pro interpretaci XML reprezentace kodu vytvorene
                                    v prvni fazi skriptem parse.php. V pripade neuvedeni tohoto argumentu se
                                    pouzije soubor interpreter.py z aktualniho adresare.
          --parse-only              Bude testovan pouze skript parse.php (analyzator jazyka IPPcode22).
          --int-only                Bude testovan pouze skript interpreter.py (interpret XML reprezentace jazyka).
          --jexampath=path          Cesta k adresari obsahujicimu soubory jexamxml.jar s JAR balickem obsahujicim
                                    nastroj A7Soft JExamXML a options s konfiguraci pro tento nastroj. Pokud neni
                                    tento argument specifikovan, je pouzit adresar /pub/courses/ipp/jexamxml/.
          --noclean                 Behem cinnosti skriptu nebudou mazany pomocne soubory (napr. s mezivysledky
                                    po zpracovani zdrojovych kodu nastrojem parse.php).
          
          Argumenty --parse-only a --int-only nesmi byt kombinovany.
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
