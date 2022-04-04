<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Entity;

use Test\Enum\TestStatus;

/**
 * Representation of ran test
 */
class Test
{

    /**
     * @var TestCase Test case defining the test
     */
    private TestCase $testCase;
    /**
     * @var TestStatus End status of this test
     */
    private TestStatus $status;
    /**
     * @var int Exit code of the tested script
     */
    private int $exitCode;

    /**
     * Class constructor
     *
     * @param TestCase $testCase Test case defining this test
     * @param TestStatus $status How the test ended?
     * @param int $exitCode Exit code of the tested script
     */
    public function __construct(TestCase $testCase, TestStatus $status, int $exitCode)
    {
        $this->testCase = $testCase;
        $this->status = $status;
        $this->exitCode = $exitCode;
    }

    /**
     * Getter for test name
     *
     * @return string Test name
     */
    public function getName(): string
    {
        return $this->testCase->getName();
    }

    /**
     * Getter for fully qualified test name
     *
     * @return string Test name with namespace prefix
     */
    public function getFullyQualifiedName(): string
    {
        return "{$this->testCase->getNamespace()}/{$this->testCase->getName()}";
    }

    /**
     * Getter for test namespace
     *
     * @return string Path to the test from testing root directory
     */
    public function getNamespace(): string
    {
        return $this->testCase->getNamespace();
    }

    /**
     * Getter for test reference exit code
     *
     * @return int Reference exit code
     */
    public function getReferenceExitCode(): int
    {
        return $this->testCase->getReferenceExitCode();
    }

    /**
     * Getter for test reference output
     *
     * @return string Reference output of the test
     */
    public function getReferenceOutput(): string
    {
        return file_get_contents($this->testCase->getReferenceOutputFile());
    }

    /**
     * Getter for test end status
     *
     * @return TestStatus End status of the test
     */
    public function getStatus(): TestStatus
    {
        return $this->status;
    }

    /**
     * Getter for tested script's exit code
     *
     * @return int Exit code of the tested script
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Getter for output of the tested script
     *
     * @return string Real output of the tested script
     */
    public function getOutput(): string
    {
        // TODO: implement this method
    }
}
