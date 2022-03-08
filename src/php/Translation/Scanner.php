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

/**
 * Scanner for reading and parsing input into tokens (+ included lexical analysis)
 */
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
     * @var int Current position in read line
     */
    private int $linePos = 0;
    /**
     * @var string|bool Current processed line (last read one) or false at the end of the file
     */
    private string|bool $readLine = "";
    /**
     * @var int State of the last scanning (calling nextToken() method)
     */
    private int $lastState = self::INIT;

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

        // There isn't a way to return char back to the stream, so we need to load line-by-line
        // and processing loaded lines with the usage of "pointer". Scanner could end at the middle
        // of the line, so the line needs to be saved in object for processing the rest of it
        // in the future call of this method. Instead of ungetc() we can now just decrement the
        // help pointer, and it has a similar effect.
        while($this->readLine !== false) {
            // Read new line if needed
            if($this->linePos >= strlen($this->readLine)) {
                $this->linePos = 0; // New line --> set pointer to start
                if(($this->readLine = fgets($this->stdin)) === false) {
                    // We reached the end of the file
                    return null;
                }

                // For cases end of line char is missing at the end of the file
                if($this->readLine[-1] != "\n") {
                    $this->readLine .= "\n";
                }
            }

            // Processing the line by chars by modified finite state machine
            for(; $this->linePos < strlen($this->readLine); $this->linePos++) {
                $readChar = $this->readLine[$this->linePos];

                switch($state) {
                    case self::INIT:
                        if($readChar == "\n") {
                            $state = self::EOL;
                        } else if(ctype_space($readChar)) {
                            $state = self::INIT;
                        } else if($readChar == '#') {
                            $state = self::COMMENT;
                        } else {
                            $state = self::VALUE;

                            // This char belongs to the value
                            $this->linePos--;
                        }
                        break;
                    case self::COMMENT:
                        if($readChar == "\n") {
                            $state = self::EOL;
                        } else {
                            $state = self::COMMENT;
                        }
                        break;
                    case self::VALUE:
                        if(!ctype_space($readChar) && $readChar != '#') {
                            $state = self::VALUE;
                            $readValue .= $readChar;
                        } else {
                            $this->lastState = self::VALUE;

                            return $this->makeToken($readValue);
                        }
                        break;
                    case self::EOL:
                        // This is the modification of the FSM (general FSM can't use 2 states),
                        // the second state is from previous processing, it's basically
                        // impossible, too. We need this to divide end of whitespace row (without
                        // special meaning) and the end of the row with instruction
                        if($this->lastState == self::VALUE) {
                            $this->lastState = self::EOL;

                            return new Token(TokenType::END);
                        } else {
                            $state = self::INIT;
                            // There could be anything including the first char of the value
                            $this->linePos--;
                            break;
                        }
                }
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
        if(preg_match('%^[TLG]F@[a-zA-Z_\-$&\%*!?][a-zA-Z_\-$&\%*!?\d]*$%', $value) === 1) {
            return $token->setArgument(new Argument(ArgType::VAR, $value));
        }

        // Int literal
        if(preg_match('%^int@([+-]?(?:\d+|0[xX][\da-fA-F]+))$%', $value, $matches) === 1) {
            if(intval($matches[1], 0) != 0 || preg_match('%^0+$%', $matches[1]) === 1) {
                return $token->setArgument(new Argument(ArgType::INT, $matches[1]));
            } else {
                throw new LexicalErrorException("Numeric literal '$matches[1]' has bad number format");
            }
        }

        // String literal
        if(preg_match('%^string@([^\s#]*)$%', $value, $matches) === 1) {
            // Cannot contain backslash char except \XXX form, where X is a number 0-9
            if(preg_match('%\\\\(?!\d{3})%', $matches[1]) === 0) {
                return $token->setArgument(new Argument(ArgType::STRING, $matches[1]));
            } else {
                throw new LexicalErrorException('Backslash char can be only used for escape sequences');
            }
        }

        // Bool literal
        if(preg_match('%^bool@(true|false)$%', $value, $matches) === 1) {
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
        if(preg_match('%^[a-zA-Z_\-$&\%*!?][a-zA-Z_\-$&\%*!?\d]*$%', $value) === 1) {
            return $token->setArgument(new Argument(ArgType::LABEL, $value));
        }

        // IPPcode22 header
        if($value == '.IPPcode22') {
            return $token->setHeader();
        }

        throw new LexicalErrorException('Invalid opcode / argument value');
    }
}
