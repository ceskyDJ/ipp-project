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
 * Exception for non-existing entered directory/file or directory/file where the script hasn't got access to
 */
class InvalidDirOrFileArgException extends Exception
{

}
