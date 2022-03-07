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
enum ArgType: string
{

    /**
     * Numeric (integer) literal
     */
    case INT = 'int';
    /**
     * Boolean value
     */
    case BOOL = 'bool';
    /**
     * String literal
     */
    case STRING = 'string';
    /**
     * Special nil value
     */
    case NIL = 'nil';

    /**
     * Label name (for jumps and calls)
     */
    case LABEL = 'label';
    /**
     * Data type name (of IPPcode22 language)
     */
    case TYPE = 'type';
    /**
     * Variable identifier
     */
    case VAR = 'var';

    /**
     * Returns enum item in savable form
     *
     * @return string External form of the enumerated item (for using as a string)
     */
    public function save(): string
    {
        return $this->value;
    }
}
