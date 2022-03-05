<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception for too many of too few input arguments from CLI has been given
 */
class BadNumberOfInputArgsException extends Exception
{

}