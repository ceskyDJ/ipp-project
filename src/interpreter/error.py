# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

from enum import IntEnum


class ExitCode(IntEnum):
    """''Standard'' exit codes for this project"""

    SUCCESS = 0
    """Early exiting but everything have gone well"""
    WRONG_INPUT_ARGS = 10
    """Missing required input argument, using forbidden combination of arguments or argument with a bad value"""
    INPUT_FILE_ERROR = 11
    """Error when opening input file (existence, permissions, ...)"""
    OUTPUT_FILE_ERROR = 12
    """Error when opening output file (existence, permissions, ...), writing error"""
    NOT_WELL_FORMED_XML = 31
    """Bad XML format (not well-formed)"""
    BAD_XML_STRUCTURE = 32
    """XML structure diffs from convention in assignment (bad element position, negative instruction order, etc.)"""
    SEMANTIC_ERROR = 52
    """Error detected by semantic analysis (usage of undefined label, variable redefinition, etc.)"""
    BAD_OPERAND_TYPES = 53
    """Runtime: Bad types of operands"""
    NON_EXISTING_VARIABLE = 54
    """Runtime: Access to non-existing variable"""
    NON_EXISTING_FRAME = 55
    """Runtime: Trying to use non-existing memory frame (reading from the empty memory frame stack, etc.)"""
    MISSING_VALUE = 56
    """Runtime: Wanted value is missing (from variable, data stack or stack frame)"""
    BAD_OPERAND_VALUE = 57
    """Runtime: Bad value of operand (zero division, bad exit code number, etc.)"""
    BAD_STRING_USAGE = 58
    """Runtime: Bad usage of (working with) a string value"""
    INTERNAL_ERROR = 99
    """Error independent of user input (memory allocation, etc.)"""


class InvalidInputArgException(Exception):
    """Exception for invalid CLI input argument"""
    pass


class TooManyInputArgsException(Exception):
    """Exception for too many input arguments specified (--help must be alone)"""
    pass


class MissingRequiredInputArgException(Exception):
    """Exception for missing required CLI input argument"""
    pass


class InvalidFileArgException(Exception):
    """Exception for invalid (non-existing, etc.) file entered as input argument"""
    pass


class UsingUndefinedLabelException(Exception):
    """Exception for trying to use undefined label in jump/call instructions"""
    pass


class PopEmptyStackException(Exception):
    """Exception for trying to pop from an empty stack"""
    pass


class EmptyLocalMemoryException(Exception):
    """Exception for trying to access an empty local memory (with no memory frame in)"""
    pass


class GetValueFromNotInitVarException(Exception):
    """Exception for trying to get a value from an uninitialized variable (it has no value yet)"""
    pass


class NonExistingVarException(Exception):
    """Exception for trying to use non-existing variable"""
    pass


class BadInstructionOrderException(Exception):
    """Exception for duplicate or negative instruction order"""
    pass


class MissingInstructionArgException(Exception):
    """Exception for missing instruction argument"""
    pass


class BadXmlStructureException(Exception):
    """Exception for errors in XML program representation structure (elements in a wrong place, etc.)"""
    pass


class XmlParsingErrorException(Exception):
    """Exception for XML parsing error due to well-formed XML"""
    pass


class InvalidDataTypeException(Exception):
    """Exception for invalid data type of operand of the instruction"""
    pass


class UsingUndefinedMemoryFrameException(Exception):
    """Exception for using undefined memory frame"""
    pass


class VariableRedefinitionException(Exception):
    """Exception for defining variable with name that has already defined variable"""
    pass


class TooFewInstructionArgsException(Exception):
    """Exception for too many input arguments (more than instruction wants)"""
    pass


class ZeroDivisionException(Exception):
    """Exception for trying to divide by zero"""
    pass


class ExitValueOutOfRangeException(Exception):
    """Exception for invalid value in EXIT instruction that is outside <0, 49>"""
    pass


class InvalidAsciiPositionException(Exception):
    """Exception for trying to convert non-ASCII position to char with ASCII"""
    pass


class IndexingOutsideStringException(Exception):
    """Exception for accessing position (char) outside the string"""
    pass


class InvalidInstructionOpCode(Exception):
    """Exception for invalid instruction operation code"""
    pass


class InvalidInstructionArgumentValueException(Exception):
    """Exception for invalid value in instruction argument"""
    pass


class DuplicateLabelException(Exception):
    """Exception for duplicate labels"""
    pass
