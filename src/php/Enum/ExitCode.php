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
 *
 * This isn't a regular enum class because, in the case of enum classes,
 * elements can't be used as public constants (like in this case).
 * For example, when you want to use exit(ExitCode::SUCCESS), it won't
 * work with the enum class.
 */
class ExitCode
{

    /**
     * Early exiting but everything have gone well
     */
    public const SUCCESS = 0;
    /**
     * Missing required input argument, using forbidden combination of arguments or argument with a bad value
     */
    public const WRONG_INPUT_ARGS = 10;
    /**
     * Error when opening input file (existence, permissions, ...)
     */
    public const INPUT_FILE_ERROR = 11;
    /**
     * Error when opening output file (existence, permissions, ...), writing error
     */
    public const OUTPUT_FILE_ERROR = 12;
    /**
     * Invalid or missing header in IPPcode22 source code
     */
    public const INVALID_HEADER = 21;
    /**
     * Invalid or missing operation code in IPPcode22 source code
     */
    public const INVALID_OPCODE = 22;
    /**
     * Other lexical or syntax error in IPPcode22 source code
     */
    public const OTHER_LEX_SYNTAX_ERROR = 23;
    /**
     * Error independent of user input (memory allocation, etc.)
     */
    public const INTERNAL_ERROR = 99;
}