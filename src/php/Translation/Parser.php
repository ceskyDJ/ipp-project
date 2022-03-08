<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Instruction;
use App\Enum\TokenType;
use App\Exceptions\InvalidHeaderException;
use App\Exceptions\InvalidOpCodeException;
use App\Exceptions\LexicalErrorException;
use App\Exceptions\SyntaxErrorException;

/**
 * Syntactic analyzer parses tokens into object representation of instructions
 * This class uses Scanner for preparing tokens from input and Generator for generating
 * final output code
 */
class Parser
{

    /**
     * Initial state of FSM
     */
    private const INIT = 0;
    /**
     * FSM state for reading IPPcode22 header
     */
    private const HEADER = 1;
    /**
     * FSM state for reading source code (its body). Header is already processed at this time
     */
    private const CODE = 2;
    /**
     * FSM state for constructing instruction
     */
    private const INSTRUCTION = 3;

        /**
     * @var Scanner Scanner instance for preparing input for Parser
     */
    private Scanner $scanner;
    /**
     * @var Generator Generator instance for creating final code representation
     */
    private Generator $generator;

    /**
     * Class constructor
     *
     * @param Scanner $scanner Scanner instance
     * @param Generator $generator Generator instance
     */
    public function __construct(Scanner $scanner, Generator $generator)
    {
        $this->scanner = $scanner;
        $this->generator = $generator;
    }

    /**
     * Parses tokens from Scanner and instruments Generator
     *
     * @return void
     * @throws LexicalErrorException Lexical error found
     * @throws SyntaxErrorException Syntax error found
     * @throws InvalidHeaderException Invalid IPPcode22 header
     * @throws InvalidOpCodeException Invalid operation code
     */
    public function parse(): void
    {
        $state = self::INIT;
        $instruction = null;

        while(($token = $this->scanner->nextToken()) !== null) {
            switch($state) {
                case self::INIT:
                    if($token->getType() == TokenType::HEADER) {
                        $state = self::HEADER;
                    } else {
                        throw new InvalidHeaderException(".IPPcode22 header is missing or invalid");
                    }
                    break;
                case self::HEADER:
                    if($token->getType() == TokenType::END) {
                        $state = self::CODE;
                    } else {
                        throw new SyntaxErrorException("There couldn't be an instruction at the header row");
                    }
                    break;
                case self::CODE:
                    if($token->getType() == TokenType::OP_CODE) {
                        $state = self::INSTRUCTION;
                        $instruction = new Instruction($token->getOpCode());
                    } else {
                        throw new InvalidOpCodeException("Operation code is invalid or unknown");
                    }
                    break;
                case self::INSTRUCTION:
                    if($token->getType() == TokenType::ARGUMENT) {
                        $state = self::INSTRUCTION;
                        $instruction->addArgument($token->getArgument());
                    } else if($token->getType() == TokenType::END) {
                        $state = self::CODE;
                        $this->generator->addInstruction($instruction);
                    } else {
                        throw new SyntaxErrorException("Invalid instruction format");
                    }
                    break;
            }
        }

        // Add the last instruction (there is no END token after it)
        // If there wasn't any instruction on the input, the variable will contain null
        if($instruction != null) {
            $this->generator->addInstruction($instruction);
        }
    }
}
