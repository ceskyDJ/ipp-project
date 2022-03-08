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
 * Exception for missing or invalid IPPcode22 header
 */
class InvalidHeaderException extends Exception
{

}
