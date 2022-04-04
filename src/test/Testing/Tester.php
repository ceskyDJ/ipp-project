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

/**
 * Tester for managing (creating a running) tests
 */
class Tester
{

    /**
     * Creates a test suite
     *
     * @return TestCase[] Test suite (array of test cases to run)
     */
    public function createTestSuite(): array
    {
        // TODO: implement this method
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
