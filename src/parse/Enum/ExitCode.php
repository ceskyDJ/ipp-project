<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Enum;

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
     * Invalid or missing header in IPPcode22 source code
     */
    case INVALID_HEADER = 21;
    /**
     * Invalid or missing operation code in IPPcode22 source code
     */
    case INVALID_OPCODE = 22;
    /**
     * Other lexical or syntax error in IPPcode22 source code
     */
    case OTHER_LEX_SYNTAX_ERROR = 23;
    /**
     * Error independent of user input (memory allocation, etc.)
     */
    case INTERNAL_ERROR = 99;
}