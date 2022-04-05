<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Entity;

use DateInterval;
use DateTime;
use Test\Enum\TestStatus;
use Test\Exceptions\SetEndOfTestingTwiceError;

/**
 * Representation of report with executed tests
 */
class TestReport
{

    /**
     * @var Test[] Run tests
     */
    private array $runTests = [];
    /**
     * @var DateTime Time when testing started
     */
    private DateTime $start;
    /**
     * @var DateTime Time when testing ended
     */
    private ?DateTime $end = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->start = new DateTime;
    }

    /**
     * Adds a new test to the report
     *
     * @param Test $test Test to add
     *
     * @return void
     */
    public function addTest(Test $test): void
    {
        $this->runTests[] = $test;
    }

    /**
     * Set testing process as ended
     *
     * @return void
     * @throws SetEndOfTestingTwiceError Setting testing as ended more than once
     */
    public function setEnd(): void
    {
        if($this->end == null) {
            throw new SetEndOfTestingTwiceError("Testing could be set as ended only once for each test report");
        }

        $this->end = new DateTime;
    }

    /**
     * Counts number of successful tests
     *
     * @return int Number of successful tests
     */
    public function countSuccessful(): int
    {
        $successful = 0;
        foreach($this->runTests as $test) {
            if($test->getStatus() == TestStatus::SUCCESS) {
                $successful++;
            }
        }

        return $successful;
    }

    /**
     * Counts number of failed tests
     *
     * @return int Number of failed tests
     */
    public function countFailed(): int
    {
        $failed = 0;
        foreach($this->runTests as $test) {
            if($test->getStatus() == TestStatus::BAD_EXIT_CODE || $test->getStatus() == TestStatus::BAD_OUTPUT) {
                $failed++;
            }
        }

        return $failed;
    }

    /**
     * Counts the length of the testing process
     *
     * @return DateInterval The length of the testing process as time interval
     */
    public function countTestingLength(): DateInterval
    {
        return $this->end->diff($this->start, true);
    }

    /**
     * Getter for run tests
     *
     * @return Test[] Run tests
     */
    public function getTests(): array
    {
        return $this->runTests;
    }
}
