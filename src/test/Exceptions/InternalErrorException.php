<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Exceptions;

use Exception;

/**
 * Exception for any internal error that can't be automatically solved by the script
 */
class InternalErrorException extends Exception
{

}
