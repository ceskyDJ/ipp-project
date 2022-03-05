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
use App\Enum\ArgType;
use DOMDocument;
use DOMElement;
use DOMException;

/**
 * Generator of final code representation
 *
 * This class prepares and processes script's output.
 * The output is composed of parsed IPPcode22 instructions
 * into an XML representation.
 */
class Generator
{

    /**
     * @var DOMDocument Library for working with XML
     */
    private DOMDocument $xml;

    /**
     * @var DOMElement Root XML element representing resulting program
     */
    private DOMElement $program;
    /**
     * @var int Number of currently processed instruction (for generating instruction order)
     */
    private int $instructionNumber;

    /**
     * Output manager's constructor
     */
    public function __construct()
    {
        // Dependencies
        $this->xml = new DOMDocument('1.0', 'UTF-8');
        $this->xml->formatOutput = true;

        $this->instructionNumber = 1;
        $this->program = $this->initRootElement();
    }

    /**
     * Initializes root XML element (program)
     *
     * @return DOMElement Prepared root XML element
     */
    private function initRootElement(): DOMElement
    {
        try {
            $program = $this->xml->createElement("program");
            $program->setAttribute("language", "IPPcode22");

            // Insert into final document
            $this->xml->appendChild($program);

            return $program;
        }
        catch(DOMException) {
        }
    }

    /**
     * Adds the instruction to the program (output XML resp.)
     *
     * @param Instruction $instruction Instruction for adding
     *
     * @return void
     */
    public function addInstruction(Instruction $instruction): void
    {
        try {
            $xmlInstruction = $this->xml->createElement('instruction');
            $xmlInstruction->setAttribute('order', (string)$this->instructionNumber++);
            $xmlInstruction->setAttribute('opcode', $instruction->getOpCode()->save());

            // Add arguments
            $i = 1;
            foreach($instruction->getArguments() as $argument) {
                // Some types of argument require escaping their values
                $value = match ($argument->getType()) {
                    ArgType::VAR, ArgType::STRING => $this->escape($argument->getValue()),
                    default => $argument->getValue()
                };

                $xmlArgument = $this->xml->createElement(sprintf('arg%d', $i++), $value);
                $xmlArgument->setAttribute('type', $argument->getType()->save());

                // Add to the instruction
                $xmlInstruction->appendChild($xmlArgument);
            }

            // Add to the program
            $this->program->appendChild($xmlInstruction);
        }
        catch(DOMException) {
        }
    }

    /**
     * Returns resulting generated XML code
     *
     * @return string Generated XML code
     */
    public function writeXml(): string
    {
        return $this->xml->saveXML();
    }

    /**
     * Escapes the value according to XML needing
     *
     * @param string $value Value for escaping
     *
     * @return string Escaped value
     */
    private function escape(string $value): string
    {
        return str_replace(['<', '>', '&', '\'', '"'], ['&lt;', '&gt;', '&amp;', '&apos;', '&quot;'], $value);
    }
}