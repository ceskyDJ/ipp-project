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
 * Methods of testing saying what will be tested
 */
enum TestStyle
{

    /**
     * Test only parsing script (parse.php)
     */
    case PARSE;
    /**
     * Test only interpreter script (interpreter.py)
     */
    case INTERPRETER;
    /**
     * Test both scripts (parse.php and interpreter.py)
     */
    case BOTH;
}
