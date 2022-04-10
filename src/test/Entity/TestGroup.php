<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Entity;

use Iterator;
use Test\Enum\TestStatus;

/**
 * Iterable container of taken tests
 */
class TestGroup implements Iterator
{

    /**
     * @var string Name of the group
     */
    private string $name;
    /**
     * @var TakenTest[] Stored similar tests composed into this group
     */
    private array $storedTests = [];
    /**
     * @var int Current position (for iterator)
     */
    private int $position = 0;

    /**
     * Class constructor
     *
     * @param string $name Name of the group
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Adds a test to the group
     *
     * @param TakenTest $test Test to add
     *
     * @return void
     */
    public function addTest(TakenTest $test): void
    {
        $this->storedTests[] = $test;
    }

    /**
     * Counts number of successful tests
     *
     * @return int Number of successful tests
     */
    public function countSuccessful(): int
    {
        $successful = 0;
        foreach($this->storedTests as $test) {
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
        foreach($this->storedTests as $test) {
            if($test->getStatus() == TestStatus::BAD_EXIT_CODE || $test->getStatus() == TestStatus::BAD_OUTPUT) {
                $failed++;
            }
        }

        return $failed;
    }

    /**
     * Counts number of stored tests
     *
     * @return int Number of stored tests in the group
     */
    public function countTests(): int
    {
        return sizeof($this->storedTests);
    }

    /**
     * Getter for group name
     *
     * @return string Group name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns currently selected test
     *
     * @return TakenTest Current taken test
     */
    public function current(): TakenTest
    {
        return $this->storedTests[$this->position];
    }

    /**
     * Selects next test
     *
     * @return void
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Returns position of the selected test
     *
     * @return int Position of the selected test
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Checks if set position points to some test
     *
     * @return bool Is current position valid?
     */
    public function valid(): bool
    {
        return $this->position < sizeof($this->storedTests);
    }

    /**
     * Selects the first test
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
