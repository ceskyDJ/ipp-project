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
