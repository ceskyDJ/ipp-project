<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Enum;

/**
 * End status of the run test
 */
enum TestStatus
{

    /**
     * Test was successful
     */
    case SUCCESS;
    /**
     * Test failed on bad exit code of tested scripts (or one of tested scripts)
     */
    case BAD_EXIT_CODE;
    /**
     * Test failed on bad output (difference tool detected that output isn't equal to the reference one)
     */
    case BAD_OUTPUT;
}
