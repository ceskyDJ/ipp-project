<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Parse\Entity;

use Parse\Enum\OpCode;
use Parse\Enum\TokenType;

/**
 * Token for communication between scanner and parser
 */
class Token
{

    /**
     * @var TokenType Type of the token (what value is stored in)
     */
    private TokenType $type;
    /**
     * @var Argument|OpCode|null Stored value
     */
    private Argument|OpCode|null $value = null;

    /**
     * Class constructor
     *
     * Setting attributes by constructor is primarily for non-value token types (HEADER, END).
     * For value type, setter method could be used
     *
     * @param TokenType|null $type Token type (optional)
     */
    public function __construct(?TokenType $type = null)
    {
        if($type != null) {
            $this->type = $type;
        }
    }

    /**
     * Getter for token type
     *
     * @return TokenType Token type
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * Returns stored operation code
     *
     * @return OpCode Operation code
     */
    public function getOpCode(): OpCode
    {
        return $this->value;
    }

    /**
     * Returns stored argument
     *
     * @return Argument Stored instruction argument
     */
    public function getArgument(): Argument
    {
        return $this->value;
    }

    /**
     * Set token to store given operation code
     *
     * @param OpCode $opCode Operation code to store
     *
     * @return Token Own instance
     */
    public function setOpCode(OpCode $opCode): Token
    {
        $this->type = TokenType::OP_CODE;
        $this->value = $opCode;

        return $this;
    }

    /**
     * Set token to store given argument
     *
     * @param Argument $argument Argument to store
     *
     * @return Token Own instance
     */
    public function setArgument(Argument $argument): Token
    {
        $this->type = TokenType::ARGUMENT;
        $this->value = $argument;

        return $this;
    }

    /**
     * Set token as END token
     *
     * @see TokenType
     * @return Token Own instance
     */
    public function setEnd(): Token
    {
        $this->type = TokenType::END;

        return $this;
    }

    /**
     * Set token as HEADER token
     *
     * @see TokenType
     * @return $this Own instance
     */
    public function setHeader(): Token
    {
        $this->type = TokenType::HEADER;

        return $this;
    }
}
