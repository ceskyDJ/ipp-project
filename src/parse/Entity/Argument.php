<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ArgType;

/**
 * Representation of instruction's argument
 */
class Argument
{

    /**
     * @var ArgType Type of the argument (of the stored value resp.)
     */
    private ArgType $type;
    /**
     * @var string Stored value
     */
    private string $value;

    /**
     * Argument's constructor
     *
     * @param ArgType $type Type of the argument (its value's)
     * @param string $value Value to store in
     */
    public function __construct(ArgType $type, string $value)
    {
        $this->type = $type;
        $this->value = $this->normalizeValue($value, $type);
    }

    /**
     * Getter for argument's type
     *
     * @return ArgType Type of argument
     */
    public function getType(): ArgType
    {
        return $this->type;
    }

    /**
     * Getter for stored value
     *
     * @return string Value stored in the argument
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Normalizes the provided value by criteria of the argument's type
     *
     * @param string $value Value to process
     * @param ArgType $type Type of argument
     *
     * @return string Normalized value
     */
    private function normalizeValue(string $value, ArgType $type): string
    {
        return match ($type) {
            ArgType::VAR => preg_replace_callback("%^(\w{2})@(.+)$%", function ($match) {
                // Friendly names for matches (there is full match at index 0, so it's skipped)
                [, $frame, $varName] = $match;

                return strtoupper($frame) . "@{$varName}";
            }, $value),
            ArgType::BOOL => strtolower($value),
            default => $value,
        };
    }
}