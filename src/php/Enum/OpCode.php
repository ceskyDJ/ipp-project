<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 *
 * @noinspection SpellCheckingInspection
 */

declare(strict_types=1);

namespace App\Enum;

use ValueError;

/**
 * Supported operation codes
 */
enum OpCode
{

    /**
     * Syntax: MOVE <var> <symb>
     */
    case MOVE;
    /**
     * Syntax: CREATEFRAME
     */
    case CREATEFRAME;
    /**
     * Syntax: PUSHFRAME
     */
    case PUSHFRAME;
    /**
     * Syntax: POPFRAME
     */
    case POPFRAME;
    /**
     * Syntax: DEFVAR <var>
     */
    case DEFVAR;
    /**
     * Syntax: CALL <label>
     */
    case CALL;
    /**
     * Syntax: RETURN
     */
    case RETURN;
    /**
     * Syntax: PUSHS <symb>
     */
    case PUSHS;
    /**
     * Syntax: POPS <var>
     */
    case POPS;
    /**
     * Syntax: <var> <symb1> <symb2>
     */
    case ADD;
    /**
     * Syntax: <var> <symb1> <symb2>
     */
    case SUB;
    /**
     * Syntax: <var> <symb1> <symb2>
     */
    case MUL;
    /**
     * Syntax: IDIV <var> <symb1> <symb2>
     */
    case IDIV;
    /**
     * Syntax: LT <var> <symb1> <symb2>
     */
    case LT;
    /**
     * Syntax: GT <var> <symb1> <symb2>
     */
    case GT;
    /**
     * Syntax: EQ <var> <symb1> <symb2>
     */
    case EQ;
    /**
     * Syntax: AND <var> <symb1> <symb2>
     */
    case AND;
    /**
     * Syntax: OR <var> <symb1> <symb2>
     */
    case OR;
    /**
     * Syntax: NOT <var> <symb1> <symb2>
     */
    case NOT;
    /**
     * Syntax: INT2CHAR <var> <symb>
     */
    case INT2CHAR;
    /**
     * Syntax: STRI2INT <var> <symb1> <symb2>
     */
    case STRI2INT;
    /**
     * Syntax: READ <var> <type>
     */
    case READ;
    /**
     * Syntax: WRITE <symb>
     */
    case WRITE;
    /**
     * Syntax: CONCAT <var> <symb1> <symb2>
     */
    case CONCAT;
    /**
     * Syntax: STRLEN <var> <symb>
     */
    case STRLEN;
    /**
     * Syntax: GETCHAR <var> <symb1> <symb2>
     */
    case GETCHAR;
    /**
     * Syntax: SETCHAR <var> <symb1> <symb2>
     */
    case SETCHAR;
    /**
     * Syntax: TYPE <var> <symb>
     */
    case TYPE;
    /**
     * Syntax: LABEL <label>
     */
    case LABEL;
    /**
     * Syntax: JUMP <label>
     */
    case JUMP;
    /**
     * Syntax: JUMPIFEQ <label> <symb1> <symb2>
     */
    case JUMPIFEQ;
    /**
     * Syntax: JUMPIFNEQ <label> <symb1> <symb2>
     */
    case JUMPIFNEQ;
    /**
     * Syntax: EXIT <symb>
     */
    case EXIT;
    /**
     * Syntax: DPRINT <symb>
     */
    case DPRINT;
    /**
     * Syntax: BREAK
     */
    case BREAK;

    /**
     * Returns enum item in savable form
     *
     * @return string External form of the enumerated item (for using as a string)
     */
    public function save(): string
    {
        return $this->name;
    }

    /**
     * Returns corresponding enum case from its name
     *
     * @param string $name Name of the enum case
     *
     * @return OpCode|null Corresponding enum case or null if invalid case name
     * @throws ValueError Invalid enum case name
     */
    public static function from(string $name): ?OpCode
    {
        $cases = self::cases();

        if(($index = array_search($name, $cases)) !== false) {
            return $cases[$index];
        } else {
            throw new ValueError("Enum case with name $name doesn't exist");
        }
    }
}