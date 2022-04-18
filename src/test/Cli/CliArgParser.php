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
use Test\Enum\TestStyle;
use Test\Exceptions\BadNumberOfInputArgsException;
use Test\Exceptions\InvalidDirOrFileArgException;
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
     * @throws InvalidDirOrFileArgException Missing  directory/file or directory/file the script hasn't got access to
     */
    public function __construct(int $argc, array $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;

        $this->parseCliArgs();

        if(key_exists("help", $this->parsedArgs)) {
            $this->writeHelp();
        }

        $this->setDefaults();
        $this->checkDirectoriesAndFiles();
    }

    /**
     * Parses input arguments from CLI
     *
     * @return void
     * @throws BadNumberOfInputArgsException Too many input args given
     * @throws InvalidInputArgValueException Missing required value of argument or excess value of switch
     */
    private function parseCliArgs(): void
    {
        $shortSwitches = '';
        // All switches have optional value (::), it is for better input validation only
        $longSwitches = [
            'help::',
            'directory:',
            'recursive::',
            'parse-script:',
            'int-script:',
            'parse-only::',
            'int-only::',
            'jexampath:',
            'noclean::'
        ];
        $usedInputArgs = 0;

        $this->parsedArgs = getopt($shortSwitches, $longSwitches, $usedInputArgs);

        $this->checkInputArgs($usedInputArgs);

        // Better test style (composition of parse-only and int-only)
        if(isset($this->parsedArgs['parse-only'])) {
            $this->parsedArgs['test-style'] = TestStyle::PARSE;
        }
        else if(isset($this->parsedArgs['int-only'])) {
            $this->parsedArgs['test-style'] = TestStyle::INTERPRETER;
        }
        else {
            $this->parsedArgs['test-style'] = TestStyle::BOTH;
        }

        unset($this->parsedArgs['parse-only']);
        unset($this->parsedArgs['int-only']);
    }

    /**
     * Checks input arguments
     *
     * @param int $usedInputArgs Number of arguments used when parsing
     *
     * @return void
     * @throws BadNumberOfInputArgsException Too many input args given
     * @throws InvalidInputArgValueException Missing required value of argument or excess value of switch
     */
    private function checkInputArgs(int $usedInputArgs): void
    {
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
            // Arguments with required value aren't in the parsed arguments array, but they are in argv
            // Arguments with entered value are in the parsed arguments array
            if(!isset($this->parsedArgs[$argument]) && in_array("--$argument", $this->argv)) {
                throw new InvalidInputArgValueException("--$argument argument must have a value");
            }
        }

        // Remove end slash from directory arguments
        $directoryArguments = ['directory', 'jexampath'];
        foreach($directoryArguments as $argument) {
            if(!isset($this->parsedArgs[$argument])) {
                continue;
            }

            $this->parsedArgs[$argument] = rtrim($this->parsedArgs[$argument], '/');
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
        $this->parsedArgs['directory'] = $this->parsedArgs['directory'] ?? getcwd();
        $this->parsedArgs['parse-script'] = $this->parsedArgs['parse-script'] ?? 'parse.php';
        $this->parsedArgs['int-script'] = $this->parsedArgs['int-script'] ?? 'interpret.py';
        $this->parsedArgs['jexampath'] = $this->parsedArgs['jexampath'] ?? '/pub/courses/ipp/jexamxml';
    }

    /**
     * Checks directory and file input arguments
     *
     * @return void
     * @throws InvalidDirOrFileArgException Missing directory/file or directory/file the script hasn't got access to
     */
    private function checkDirectoriesAndFiles(): void
    {
        // Check validity of directory arguments
        $directoryArguments = ['directory', 'jexampath'];
        foreach($directoryArguments as &$argument) {
            $directory = $this->parsedArgs[$argument];
            if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
                throw new InvalidDirOrFileArgException("Directory '$directory' in --$argument isn't valid.");
            }

            // Convert to better path structure
            $this->parsedArgs[$argument] = realpath($this->parsedArgs[$argument]);
        }
        unset($argument);

        // Check validity of source files arguments
        $scriptFileArguments = ['parse-script', 'int-script'];
        foreach($scriptFileArguments as $argument) {
            if(!isset($this->parsedArgs[$argument])) {
                continue;
            }

            $file = $this->parsedArgs[$argument];
            if(!file_exists($file) || !is_file($file) || !is_readable($file)) {
                throw new InvalidDirOrFileArgException("File '$file' in --$argument isn't valid.");
            }

            // Convert to better path structure
            $this->parsedArgs[$argument] = realpath($this->parsedArgs[$argument]);
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
        test.php je skript pro automaticke testovani skriptu parse.php a interpret.py. Jsou vyuzivany
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
          --parse-script=file       Cesta k souboru skriptu pro analyzu zdrojoveho kodu v jazyce IPPcode22.
                                    Pokud neni tento argument zadan, predpoklada se soubor parse.php
                                    v aktualnim adresari.
          --int-script=file         Cesta k souboru skriptu pro interpretaci XML reprezentace kodu vytvorene
                                    v prvni fazi skriptem parse.php. V pripade neuvedeni tohoto argumentu se
                                    pouzije soubor interpret.py z aktualniho adresare.
          --parse-only              Bude testovan pouze skript parse.php (analyzator jazyka IPPcode22).
          --int-only                Bude testovan pouze skript interpret.py (interpret XML reprezentace jazyka).
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
     * Getter for directory input argument
     *
     * @return string Path to test directory
     */
    public function getDirectory(): string
    {
        return $this->parsedArgs['directory'];
    }

    /**
     * Getter for recursive input argument
     *
     * @return bool Do the recursive search of the test cases?
     */
    public function isRecursive(): bool
    {
        return isset($this->parsedArgs['recursive']);
    }

    /**
     * Getter for parse-script input argument
     *
     * @return string Path to the parse script (parse.php)
     */
    public function getParseScript(): string
    {
        return $this->parsedArgs['parse-script'];
    }

    /**
     * Getter for int-script input argument
     *
     * @return string Path to the interpreter script (interpret.py)
     */
    public function getIntScript(): string
    {
        return $this->parsedArgs['int-script'];
    }

    /**
     * Getter for jexampath input argument
     *
     * @return string Path to the directory with JExamPath program
     */
    public function getJExamPath(): string
    {
        return $this->parsedArgs['jexampath'];
    }

    /**
     * Getter for noclean input argument
     *
     * @return bool Could be temporary files preserved (not cleaned)?
     */
    public function isNoClean(): bool
    {
        return isset($this->parsedArgs['noclean']);
    }

    /**
     * Getter for test style (combination of parse-only and int-only input arguments)
     *
     * @return TestStyle What to be tested
     */
    public function getTestStyle(): TestStyle
    {
        return $this->parsedArgs['test-style'];
    }
}
