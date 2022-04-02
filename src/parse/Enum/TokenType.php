<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Parse\Enum;

/**
 * Token types informing about the value stored in token
 */
enum TokenType
{

    /**
     * Required header of IPPcode22 language
     */
    case HEADER;
    /**
     * Operation code
     */
    case OP_CODE;
    /**
     * Instruction argument
     */
    case ARGUMENT;
    /**
     * End of the instruction
     */
    case END;
}
