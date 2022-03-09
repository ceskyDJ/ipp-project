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
 * Types of instructions' arguments
 */
enum ArgType: int
{

    /**
     * Numeric (integer) literal
     */
    case INT = 1;
    /**
     * Boolean value
     */
    case BOOL = 2;
    /**
     * String literal
     */
    case STRING = 4;
    /**
     * Special nil value
     */
    case NIL = 8;

    /**
     * Label name (for jumps and calls)
     */
    case LABEL = 16;
    /**
     * Data type name (of IPPcode22 language)
     */
    case TYPE = 32;
    /**
     * Variable identifier
     */
    case VAR = 64;

    /**
     * Returns bit flags for constant (literal) value
     *
     * @return int Bit flags for constant
     */
    public static function constant(): int
    {
        return self::INT->value | self::BOOL->value | self::STRING->value | self::NIL->value;
    }

    /**
     * Returns bit flags for symbol
     *
     * @return int Bit flags for symbol
     */
    public static function symbol(): int
    {
        return self::constant() | self::VAR->value;
    }

    /**
     * Returns bit flags for variable with specific type or constant with that type
     *
     * @param ArgType $type Variable/constant type
     *
     * @return int Bit flags for variable/constant of the type
     */
    public static function typedConstVar(ArgType $type): int
    {
        return $type->value | self::VAR->value;
    }
}
