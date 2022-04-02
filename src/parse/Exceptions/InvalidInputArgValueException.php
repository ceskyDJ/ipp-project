<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Parse\Exceptions;

use Exception;

/**
 * Exception for invalid (missing, extra, wrong) value of CLI input argument
 */
class InvalidInputArgValueException extends Exception
{

}
