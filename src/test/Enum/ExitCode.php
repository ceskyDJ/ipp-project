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
 * "Standard" exit codes for this project
 */
enum ExitCode: int
{

    /**
     * Early exiting but everything have gone well
     */
    case SUCCESS = 0;
    /**
     * Missing required input argument, using forbidden combination of arguments or argument with a bad value
     */
    case WRONG_INPUT_ARGS = 10;
    /**
     * Error when opening input file (existence, permissions, ...)
     */
    case INPUT_FILE_ERROR = 11;
    /**
     * Error when opening output file (existence, permissions, ...), writing error
     */
    case OUTPUT_FILE_ERROR = 12;
    /**
     * Entered directory or file doesn't exist or the script hasn't got access to it
     */
    case INVALID_DIR_FILE_ARG = 41;
    /**
     * Error independent of user input (memory allocation, etc.)
     */
    case INTERNAL_ERROR = 99;
}
