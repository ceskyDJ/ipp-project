<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Entity;

use App\Enum\OpCode;

/**
 * Representation of instruction
 */
class Instruction
{

    /**
     * @var OpCode Instruction's operation code (de facto name of the instruction)
     */
    private OpCode $opCode;
    /**
     * @var Argument[] Array instruction's arguments
     */
    private array $arguments;

    /**
     * Instruction's constructor
     *
     * @param OpCode $opCode Operation code (de facto name of the instruction)
     * @param Argument[] $arguments Array of arguments (not required)
     */
    public function __construct(OpCode $opCode, array $arguments = [])
    {
        $this->opCode = $opCode;
        $this->arguments = $arguments;
    }

    /**
     * Adds an argument to the instruction
     *
     * @param Argument $argument Argument to add
     *
     * @return void
     */
    public function addArgument(Argument $argument): void
    {
        $this->arguments[] = $argument;
    }

    /**
     * Getter for operation code
     *
     * @return OpCode Operation code of the instruction
     */
    public function getOpCode(): OpCode
    {
        return $this->opCode;
    }

    /**
     * Getter for arguments
     *
     * @return Argument[] Instruction's arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}