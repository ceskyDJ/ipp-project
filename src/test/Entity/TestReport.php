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
        // TODO: implement this method
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
        // TODO: implement this method
    }

    /**
     * Counts number of failed tests
     *
     * @return int Number of failed tests
     */
    public function countFailed(): int
    {
        // TODO: implement this method
    }

    /**
     * Counts the length of the testing process
     *
     * @return DateInterval The length of the testing process as time interval
     */
    public function countTestingLength(): DateInterval
    {
        // TODO: implement this method
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
