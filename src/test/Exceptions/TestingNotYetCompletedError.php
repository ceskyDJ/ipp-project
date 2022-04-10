<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Exceptions;

use Error;

/**
 * Error for using test report as completed, but it hasn't been set as completed yet
 */
class TestingNotYetCompletedError extends Error
{

}
