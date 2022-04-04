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
 * Error for trying to set testing end more than one times
 */
class SetEndOfTestingTwiceError extends Error
{

}
