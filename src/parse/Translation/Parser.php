<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Parse\Translation;

use Parse\Entity\Argument;
use Parse\Entity\Instruction;
use Parse\Enum\ArgType;
use Parse\Enum\OpCode;
use Parse\Enum\TokenType;
use Parse\Exceptions\InvalidHeaderException;
use Parse\Exceptions\InvalidOpCodeException;
use Parse\Exceptions\LexicalErrorException;
use Parse\Exceptions\SyntaxErrorException;

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
        $opCodeExpected = false;

        try {
            while(($token = $this->scanner->nextToken($opCodeExpected)) !== null) {
                if($token == null) {
                    throw new InvalidOpCodeException("Operation code expected but it's missing or invalid");
                }

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
                            $opCodeExpected = true;
                        } else {
                            throw new SyntaxErrorException("There couldn't be an instruction at the header row");
                        }
                        break;
                    case self::CODE:
                        if($token->getType() == TokenType::OP_CODE) {
                            $state = self::INSTRUCTION;
                            $instruction = new Instruction($token->getOpCode());
                            $opCodeExpected = false;
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
                            $opCodeExpected = true;
                            $this->saveInstruction($instruction);
                        } else {
                            throw new SyntaxErrorException("Invalid instruction format");
                        }
                        break;
                }
            }
        } catch(LexicalErrorException $e) {
            if($state == self::INIT) {
                throw new InvalidHeaderException("Bad header format");
            } else {
                throw $e;
            }
        }

        // Save the last instruction (there is no END token after it)
        // If there wasn't any instruction on the input, the variable will contain null
        if($instruction != null) {
            $this->saveInstruction($instruction);
        }
    }

    /**
     * Checks an instruction and possibly sends it to generator
     *
     * @param Instruction $instruction Instruction to operate with
     *
     * @return void
     * @throws SyntaxErrorException Syntax error found
     */
    private function saveInstruction(Instruction $instruction): void
    {
        $argumentsSyntax = [];
        switch($instruction->getOpCode()) {
            case OpCode::MOVE:
            case OpCode::TYPE:
                $argumentsSyntax = [ArgType::VAR, ArgType::symbol()];
                break;
            case OpCode::CREATEFRAME:
            case OpCode::PUSHFRAME:
            case OpCode::POPFRAME:
            case OpCode::RETURN:
            case OpCode::BREAK:
                break;
            case OpCode::DEFVAR:
            case OpCode::POPS:
                $argumentsSyntax = [ArgType::VAR];
                break;
            case OpCode::CALL:
            case OpCode::LABEL:
            case OpCode::JUMP:
                $argumentsSyntax = [ArgType::LABEL];
                break;
            case OpCode::PUSHS:
            case OpCode::WRITE:
            case OpCode::DPRINT:
                $argumentsSyntax = [ArgType::symbol()];
                break;
            case OpCode::ADD:
            case OpCode::SUB:
            case OpCode::MUL:
            case OpCode::IDIV:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::INT), ArgType::typedConstVar(ArgType::INT)];
                break;
            case OpCode::LT:
            case OpCode::GT:
                $argumentsSyntax = [ArgType::VAR, ArgType::symbol(), ArgType::symbol()];
                $args = $instruction->getArguments();
                if(sizeof($args) < 3) {
                    throw new SyntaxErrorException("Too few arguments");
                }

                if(!$this->hasSameType($args[1], $args[2])) {
                    throw new SyntaxErrorException("Constants/literals in arguments must have the same type");
                }
                break;
            case OpCode::EQ:
                $argumentsSyntax = [ArgType::VAR, ArgType::symbol(), ArgType::symbol()];
                $args = $instruction->getArguments();
                if(sizeof($args) < 3) {
                    throw new SyntaxErrorException("Too few arguments");
                }

                if(!$this->hasSameTypeOrNil($args[1], $args[2])) {
                    throw new SyntaxErrorException("Constants/literals in arguments must have the same type
                    or one of them must be nil");
                }
                break;
            case OpCode::AND:
            case OpCode::OR:
            case OpCode::NOT:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::BOOL), ArgType::typedConstVar(ArgType::BOOL)];
                break;
            case OpCode::READ:
                $argumentsSyntax = [ArgType::VAR, ArgType::TYPE];
                break;
            case OpCode::CONCAT:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::STRING), ArgType::typedConstVar
                (ArgType::STRING)];
                break;
            case OpCode::STRLEN:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::STRING)];
                break;
            case OpCode::GETCHAR:
            case OpCode::STRI2INT:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::STRING), ArgType::typedConstVar
                (ArgType::INT)];
                break;
            case OpCode::SETCHAR:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::INT), ArgType::typedConstVar
                (ArgType::STRING)];
                break;
            case OpCode::JUMPIFEQ:
            case OpCode::JUMPIFNEQ:
                $argumentsSyntax = [ArgType::LABEL, ArgType::symbol(), ArgType::symbol()];
                $args = $instruction->getArguments();
                if(sizeof($args) < 3) {
                    throw new SyntaxErrorException("Too few arguments");
                }

                if(!$this->hasSameTypeOrNil($args[1], $args[2])) {
                    throw new SyntaxErrorException("Constants/literals in arguments must have the same type
                        or one of them must be nil");
                }
                break;
            case OpCode::EXIT:
                $argumentsSyntax = [ArgType::typedConstVar(ArgType::INT)];
                break;
            case OpCode::INT2CHAR:
                $argumentsSyntax = [ArgType::VAR, ArgType::typedConstVar(ArgType::INT)];
                break;
        }

        // Check arguments with prepared syntax pattern
        if(!$this->hasCorrectArguments($instruction, $argumentsSyntax)) {
            throw new SyntaxErrorException("Instruction's arguments aren't correct");
        }

        // Send instruction to the generator
        $this->generator->addInstruction($instruction);
    }

    /**
     * Checks correctness of instruction's arguments
     *
     * @param Instruction $instruction Instruction to check
     * @param ArgType[]|int[] $argumentsSyntax Syntax pattern for arguments
     *
     * @return bool Are arguments or the instruction correct?
     */
    private function hasCorrectArguments(Instruction $instruction, array $argumentsSyntax): bool
    {
        $args = $instruction->getArguments();

        if(sizeof($args) != sizeof($argumentsSyntax)) {
            return false;
        }

        $i = 0;
        foreach($instruction->getArguments() as $argument) {
            if(($argumentsSyntax[$i] instanceof ArgType) && $argumentsSyntax[$i] != $argument->getType()) {
                return false;
            }

            if((!($argumentsSyntax[$i] instanceof ArgType)) && !($argumentsSyntax[$i] & $argument->getType()->value)) {
                return false;
            }

            $i++;
        }

        return true;
    }

    /**
     * Compares types of two arguments for equality (static comparison only)
     *
     * @param Argument $first First argument
     * @param Argument $second Second argument
     *
     * @return bool Have arguments the same type (or is at least one of them a variable)?
     */
    private function hasSameType(Argument $first, Argument $second): bool
    {
        // We can only check constants/literals here
        if($first->getType() == ArgType::VAR || $second->getType() == ArgType::VAR) {
            return true;
        }

        return $first->getType() == $second->getType();
    }

    /**
     * Compares types of two arguments for equality or nil (static comparison only)
     *
     * @param Argument $first First argument
     * @param Argument $second Second argument
     *
     * @return bool Have arguments the same type or nil type (or is at least one of them a variable)?
     */
    private function hasSameTypeOrNil(Argument $first, Argument $second): bool
    {
        return $this->hasSameType($first, $second) || $first->getType() == ArgType::NIL || $second->getType() ==
            ArgType::NIL;
    }
}
