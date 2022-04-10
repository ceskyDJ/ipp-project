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
use Iterator;
use Test\Exceptions\SetEndOfTestingTwiceError;
use Test\Exceptions\TestingNotYetCompletedError;

/**
 * Representation of report with executed tests
 */
class TestReport implements Iterator
{

    /**
     * @var TestGroup[] Groups of taken tests
     */
    private array $takenTestGroups = [];
    /**
     * @var DateTime Time when testing started
     */
    private DateTime $start;
    /**
     * @var DateTime|null Time when testing ended
     */
    private ?DateTime $end = null;

    /**
     * @var int Currently selected group
     */
    private int $selectedGroup = 0;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->start = new DateTime;
    }

    /**
     * Returns group at n-th key
     *
     * @param int $position Position of the key
     *
     * @return TestGroup|null Test group at the selected key or for out of array bounds
     */
    private function getGroupAt(int $position): ?TestGroup
    {
        $keys = array_keys($this->takenTestGroups);

        // Out of bound of the array with test groups
        if($position >= sizeof($keys)) {
            return null;
        }

        return $this->takenTestGroups[$keys[$position]];
    }

    /**
     * Adds a new test to the report
     *
     * @param TakenTest $test Test to add
     *
     * @return void
     */
    public function addTest(TakenTest $test): void
    {
        $group = $test->getNamespace();

        if(!key_exists($group, $this->takenTestGroups)) {
            $this->takenTestGroups[$group] = new TestGroup($group);
        }

        $this->takenTestGroups[$group]->addTest($test);
    }

    /**
     * Set testing process as ended
     *
     * @return void
     * @throws SetEndOfTestingTwiceError Setting testing as ended more than once
     */
    public function setEnd(): void
    {
        if($this->end != null) {
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
        return array_reduce(
            $this->takenTestGroups,
            fn(int $successful, TestGroup $group) => $successful + $group->countSuccessful(),
            0
        );
    }

    /**
     * Counts number of failed tests
     *
     * @return int Number of failed tests
     */
    public function countFailed(): int
    {
        return array_reduce(
            $this->takenTestGroups,
            fn(int $failed, TestGroup $group) => $failed + $group->countFailed(),
            0
        );
    }

    /**
     * Count number of ran tests
     *
     * @return int Number of ran tests
     */
    public function countTests(): int
    {
        return array_reduce(
            $this->takenTestGroups,
            fn(int $tests, TestGroup $group) => $tests + $group->countTests(),
            0
        );
    }

    /**
     * Counts the length of the testing process
     *
     * @return DateInterval The length of the testing process as time interval
     */
    public function countTestingLength(): DateInterval
    {
        if($this->end == null) {
            throw new TestingNotYetCompletedError("Testing hasn't been completed yet. Call TestReport::setEnd()
            before this method");
        }

        return $this->end->diff($this->start, true);
    }

    /**
     * Getter for grouped taken tests
     *
     * @return TestGroup[] Grouped taken tests
     */
    public function getTestGroups(): array
    {
        return $this->takenTestGroups;
    }

    /**
     * Returns current test
     *
     * @return TakenTest Currently selected taken test
     */
    public function current(): TakenTest
    {
        $group = $this->getGroupAt($this->selectedGroup);

        return $group->current();
    }

    /**
     * Move to the next test
     *
     * @return void
     */
    public function next(): void
    {
        $currentGroup = $this->getGroupAt($this->selectedGroup);
        $currentGroup->next();

        // Current group has been completely iterated, let's move to the next one
        if(!$currentGroup->valid()) {
            $this->selectedGroup++;

            // When there is some group at the new position, reset its iterator
            $nextGroup = $this->getGroupAt($this->selectedGroup);
            $nextGroup?->rewind();
        }
    }

    /**
     * Returns number of currently processed group of tests
     *
     * @return int Number of currently processed test group
     */
    public function key(): int
    {
        return $this->selectedGroup;
    }

    /**
     * Checks if there is more tests
     *
     * @return bool Are there more tests?
     */
    public function valid(): bool
    {
        return $this->selectedGroup < sizeof($this->takenTestGroups);
    }

    /**
     * Selects the first test
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->selectedGroup = 0;

        // Reset iterator of the first group, if there is some
        $firstGroup = $this->getGroupAt(0);
        $firstGroup?->rewind();
    }
}
