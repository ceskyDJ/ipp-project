<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Entity;

/**
 * Representation of test case (guide how to run a test)
 */
class TestCase
{

    /**
     * @var string Name of the test
     */
    private string $name;
    /**
     * @var string Path from the root directory of tests (exclude this test's folder)
     */
    private string $namespace;
    /**
     * @var string Test root directory
     */
    private string $testRootDir;

    /**
     * Class constructor
     *
     * @param string $pathToTest Path to the test folder
     */
    public function __construct(string $pathToTest, string $testRootDir)
    {
        $this->testRootDir = $testRootDir;

        $this->createFromPath($pathToTest);
    }

    /**
     * Creates object's data from the test path
     *
     * @param string $pathToTest Path to the test folder
     *
     * @return void
     */
    private function createFromPath(string $pathToTest): void
    {
        // TODO: implement this method
    }

    /**
     * Getter for test name
     *
     * @return string Test name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter for test namespace
     *
     * @return string Path to the test from testing root directory
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Getter for test path
     *
     * @return string Test path (relative from current directory or absolute)
     */
    public function getPath(): string
    {
        // TODO: implement this method
    }

    /**
     * Getter for source code to test
     *
     * @return string Source code to give on the input of the tested script
     */
    public function getSourceCode(): string
    {
        // TODO: implement this method
    }

    /**
     * Getter for test input file
     *
     * @return string Path to file with input for test from root testing directory
     */
    public function getInputFile(): string
    {
        // TODO: implement this method
    }

    /**
     * Getter for test reference exit code
     *
     * @return int Reference exit code
     */
    public function getReferenceExitCode(): int
    {
        // TODO: implement this method
    }

    /**
     * Getter for test reference output file
     *
     * @return string Path to reference output file from root testing directory
     */
    public function getReferenceOutputFile(): string
    {
        // TODO: implement this method
    }
}
