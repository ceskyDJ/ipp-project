<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Argument;
use App\Entity\Token;
use App\Enum\ArgType;
use App\Enum\OpCode;
use App\Enum\TokenType;
use App\Exceptions\LexicalErrorException;
use ValueError;

class Scanner
{

    /**
     * Initial state of the finite state machine
     */
    private const INIT = 0;
    /**
     * Processing comment state of the finite state machine
     */
    private const COMMENT = 1;
    /**
     * Processing value state of the finite state machine
     */
    private const VALUE = 2;
    /**
     * End of line state of the finite state machine
     */
    private const EOL = 3;

    /**
     * @var resource File handler for standard input
     */
    private mixed $stdin;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        fclose($this->stdin);
    }

    /**
     * Gives a next loaded token
     *
     * @return Token|null Token with scanned data or null for end of file
     * @throws LexicalErrorException Lexical error in input source code
     */
    public function nextToken(): ?Token
    {
        $state = self::INIT;
        $readValue = '';

        while(($readChar = fgetc($this->stdin)) !== false) {
            switch($state) {
                case self::INIT:
                    if($readChar == "\n") {
                        $state = self::EOL;
                    } else if(ctype_space($readChar)) {
                        $state = self::INIT;
                    } else if($readChar == '#') {
                        $state = self::COMMENT;
                    } else if(ctype_print($readChar)) {
                        $state = self::VALUE;
                    } else {
                        throw new LexicalErrorException('Invalid character in source code');
                    }
                    break;
                case self::COMMENT:
                    if($readChar == "\n") {
                        $state = self::INIT;
                    } else {
                        $state = self::COMMENT;
                    }
                    break;
                case self::VALUE:
                    if(ctype_print($readChar) && $readChar != '#') {
                        $state = self::VALUE;
                        $readValue .= $readChar;
                    } else {
                        return $this->makeToken($readValue);
                    }
                    break;
                case self::EOL:
                    return new Token(TokenType::END);
            }
        }

        return null;
    }

    /**
     * Parses value into token
     *
     * @param string $value Stringy value to parse
     *
     * @return Token Created token
     * @throws LexicalErrorException Lexical error in input source code
     */
    private function makeToken(string $value): Token
    {
        $token = new Token;

        // Operation code
        try {
            return $token->setOpCode(OpCode::from(strtoupper($value)));
        } catch(ValueError) { /* Continue with parsing by trying other variants */ }

        // Argument
        // Variable
        if(preg_match('%^[TLG]F@[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%*!?\d}]*$%', $value) !== false) {
            return $token->setArgument(new Argument(ArgType::VAR, $value));
        }

        // Int literal
        if(preg_match('%^int@([+-]?(?:\d+|0[xX][\da-fA-F]+))$%', $value, $matches) !== false) {
            if(intval($matches[1], 0) != 0 || preg_match('%^0+$%', $matches[1]) === 1) {
                return $token->setArgument(new Argument(ArgType::INT, $matches[1]));
            } else {
                throw new LexicalErrorException("Numeric literal '$matches[1]' has bad number format");
            }
        }

        // String literal
        if(preg_match('%^string@([^\s#]+)$%', $value, $matches) !== false) {
            // Cannot contain backslash char except \XXX form, where X is a number 0-9
            if(preg_match('%\\\\(?!\d{3})%', $matches[1]) === false) {
                return $token->setArgument(new Argument(ArgType::STRING, $matches[1]));
            } else {
                throw new LexicalErrorException('Backslash char can be only used for escape sequences');
            }
        }

        // Bool literal
        if(preg_match('%^bool@(true|false)$%', $value, $matches) !== false) {
            return $token->setArgument(new Argument(ArgType::BOOL, $matches[1]));
        }

        // Nil literal
        if($value == 'nil@nil') {
            return $token->setArgument(new Argument(ArgType::NIL, 'nil'));
        }

        // Type
        if($value == 'int' || $value == 'string' || $value == 'bool') {
            return $token->setArgument(new Argument(ArgType::TYPE, $value));
        }

        // Label
        if(preg_match('%^[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%*!?\d]*$%', $value) !== false) {
            return $token->setArgument(new Argument(ArgType::LABEL, $value));
        }

        // IPPcode22 header
        if($value == '.IPPcode22') {
            return $token->setHeader();
        }

        throw new LexicalErrorException('Invalid opcode / argument value');
    }
}