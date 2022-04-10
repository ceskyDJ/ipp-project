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
     * @var string Path from the root directory of tests
     */
    private string $namespace;
    /**
     * @var string Test root directory
     */
    private string $testRootDir;
    /**
     * @var string Temporary directory for storing temporary help files for testing
     */
    private string $testTmpDir;

    /**
     * Class constructor
     *
     * @param string $pathToTest Path to the test universal filename (without extension)
     * @param string $testRootDir Path to the test root directory
     * @param string $testTmpDir Path to the temporary directory for storing temporary help files for testing
     */
    public function __construct(string $pathToTest, string $testRootDir, string $testTmpDir)
    {
        $this->testRootDir = $testRootDir;
        $this->testTmpDir = $testTmpDir;

        $this->createFromPath($pathToTest);
    }

    /**
     * Creates object's data from the test path
     *
     * @param string $pathToTest Path to the test universal filename (without extension)
     *
     * @return void
     */
    private function createFromPath(string $pathToTest): void
    {
        $testPathParts = explode('/', $pathToTest);

        $this->name = rtrim(array_pop($testPathParts), '/');
        $directoryWithTest = implode('/', $testPathParts);

        $this->namespace = $directoryWithTest;
    }

    /**
     * Gets file content in a safe way0
     *
     * @param string $file File name
     *
     * @return string Content of the file
     */
    private function getFileContent(string $file): string
    {
        // Create a file if not exists
        if(!file_exists($file)) {
            touch($file);
        }

        return file_get_contents($file);
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
     * @return string Path to the directory contains the test from testing root directory
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Getter for test path
     *
     * @return string Full path to the test directory (relative from current directory or absolute)
     */
    public function getPath(): string
    {
        return "$this->namespace/$this->name";
    }

    /**
     * Getter for file with source code to test
     *
     * @return string Path to file with source code to use in test
     */
    public function getSourceCodeFile(): string
    {
        return "$this->testRootDir/{$this->getPath()}.src";
    }

    /**
     * Getter for source code to test
     *
     * @return string Source code to give on the input of the tested script
     */
    public function getSourceCode(): string
    {
        return $this->getFileContent($this->getSourceCodeFile());
    }

    /**
     * Getter for test input file
     *
     * @return string Path to file with input for test from root testing directory
     */
    public function getInputFile(): string
    {
        return "$this->testRootDir/{$this->getPath()}.in";
    }

    /**
     * Getter for test input
     *
     * @return string Input to give to the tested script
     */
    public function getInput(): string
    {
        return $this->getFileContent($this->getInputFile());
    }

    /**
     * Getter for test reference exit code
     *
     * @return int Reference exit code
     */
    public function getReferenceExitCode(): int
    {
        $file = "$this->testRootDir/{$this->getPath()}.rc";

        // Create a file if not exists
        if(!file_exists($file)) {
            file_put_contents($file, 0);
        }

        return (int)$this->getFileContent($file);
    }

    /**
     * Getter for test reference output file
     *
     * @return string Path to reference output file from root testing directory
     */
    public function getReferenceOutputFile(): string
    {
        return "$this->testRootDir/{$this->getPath()}.out";
    }

    /**
     * Getter for test reference output
     * 
     * @return string Reference output for the tested script
     */
    public function getReferenceOutput(): string
    {
        return $this->getFileContent($this->getReferenceOutputFile());
    }

    /**
     * Getter for test output file
     * 
     * @return string Path to file where to save output of the (last) tested script
     */
    public function getOutputFile(): string
    {
        return posix_getcwd() . "/$this->testTmpDir/{$this->getPath()}.out";
    }

    /**
     * Getter for temporary file for output of intermediate step of the testing process
     *
     * @return string Path to the temporary file
     */
    public function getTempFile(): string
    {
        return posix_getcwd() . "/$this->testTmpDir/{$this->getPath()}.tmp";
    }
}
