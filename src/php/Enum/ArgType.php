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

    case INT = 'int';
    case BOOL = 'bool';
    case STRING = 'string';
    case NIL = 'nil';

    case LABEL = 'label';
    case TYPE = 'type';
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
