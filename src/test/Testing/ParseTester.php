<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Testing;

use Test\Entity\TakenTest;
use Test\Entity\TestCase;
use Test\Entity\TestReport;
use Test\Tools\DiffProgram;

/**
 * Concrete tester for testing script parse.php
 */
class ParseTester extends Tester
{

    /**
     * Class constructor
     *
     * @param DiffProgram $diffProgram Program for comparing files (dependency)
     * @param bool $recursive Could be tests loaded recursively?
     * @param string $testRootDir Path to the directory with test cases to process
     * @param string $tmpDir Path to the directory for storing temporary files
     * @param string $parseScript Path to the parse script
     * @param string $intScript Path to the interpreter script
     */
    public function __construct(
        DiffProgram $diffProgram,
        bool $recursive,
        string $testRootDir,
        string $tmpDir,
        string $parseScript,
        string $intScript
    ) {
        parent::__construct($diffProgram, $recursive, $testRootDir, $tmpDir, $parseScript, $intScript);
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
        $report = new TestReport;

        foreach($testSuite as $testCase) {
            $exitCode = null;
            $output = null;
            $inFile = $testCase->getSourceCodeFile();
            $outFile = $testCase->getOutputFile();

            exec("php8.1 $this->parseScript < $inFile > $outFile", $output, $exitCode);

            $testState = $this->verifyTestResult($testCase, (int)$exitCode);

            $report->addTest(new TakenTest($testCase, $testState, (int)$exitCode));
        }

        return $report;
    }
}
