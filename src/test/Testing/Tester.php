<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Testing;

use Test\Entity\TestCase;
use Test\Entity\TestReport;
use Test\Enum\TestStyle;
use Test\Exceptions\InternalErrorException;
use Test\Tools\DiffProgram;

/**
 * Tester for managing (creating a running) tests
 */
class Tester
{

    /**
     * @var DiffProgram Program for comparing files (dependency)
     */
    private DiffProgram $diffProgram;

    /**
     * @var TestStyle What to be tested
     */
    private TestStyle $testStyle;
    /**
     * @var bool Could be tests loaded recursively?
     */
    private bool $recursive;
    /**
     * @var string Directory with tests to process
     */
    private string $testRootDir;
    /**
     * @var string Directory for storing temporary files
     */
    private string $tmpDir;
    /**
     * @var string Path to the parse script (parse.php)
     */
    private string $parseScript;
    /**
     * @var string Path to the interpreter script
     */
    private string $intScript;

    /**
     * Class constructor
     *
     * @param DiffProgram $diffProgram Program for comparing files (dependency)
     * @param TestStyle $testStyle What to be tested
     * @param bool $recursive Could be tests loaded recursively?
     * @param string $testRootDir Path to the directory with test cases to process
     * @param string $tmpDir Path to the directory for storing temporary files
     * @param string $parseScript Path to the parse script
     * @param string $intScript Path to the interpreter script
     */
    public function __construct(
        DiffProgram $diffProgram,
        TestStyle $testStyle,
        bool $recursive,
        string $testRootDir,
        string $tmpDir,
        string $parseScript,
        string $intScript
    ) {
        $this->diffProgram = $diffProgram;

        $this->testStyle = $testStyle;
        $this->recursive = $recursive;
        $this->testRootDir = $testRootDir;
        $this->tmpDir = $tmpDir;
        $this->parseScript = $parseScript;
        $this->intScript = $intScript;
    }

    /**
     * Loads list of tests cases from specified folder
     *
     * @param string $path Path to folder where to search for test cases
     *
     * @return string[] Found test cases (file names without extension)
     */
    private function loadTestCasesFromFolder(string $path): array
    {
        // Load directory contents and remove '.' and '..' items
        $dirContents = scandir("$this->testRootDir/$path");
        $dirContents = array_diff($dirContents, ['.', '..']);

        $testCases = [];
        foreach($dirContents as $item) {
            // Path to the item from test root directory
            $itemPath = $path != '' ? "$path/$item" : $item;

            if(is_file("$this->testRootDir/$itemPath") && !str_starts_with($item, '.')) {
                // Files (except hidden ones) are used for testing --> add to test cases list
                // When the extension is dropped, the rest of the name is the name of test case
                $nameWithoutExtension = preg_replace("%\.[^.]+$%", '', $itemPath);
                if(!in_array($nameWithoutExtension, $testCases)) {
                    $testCases[] = $nameWithoutExtension;
                }
            } else if(is_dir("$this->testRootDir/$itemPath") && $this->recursive) {
                // When recursive searching is enabled, run recursively on directory
                $testCases = [...$testCases, ...$this->loadTestCasesFromFolder($itemPath)];
            }
        }

        return $testCases;
    }

    /**
     * Creates a test suite
     *
     * @return TestCase[] Test suite (array of test cases to run)
     */
    public function createTestSuite(): array
    {
        $testSuite = [];
        foreach($this->loadTestCasesFromFolder('') as $testCasePath) {
            $testSuite[] = new TestCase($testCasePath, $this->testRootDir, $this->tmpDir);
        }

        return $testSuite;
    }

    /**
     * Run tests from provided test suite
     *
     * @param TestCase[] $testSuite Test suite (array of test cases to run)
     *
     * @return TestReport Generated test report
     */
    public function test(array $testSuite): TestReport
    {
        // TODO: implement this method
    }
}
