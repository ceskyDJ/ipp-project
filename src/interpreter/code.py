# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022
import re
from enum import Enum
from typing import Dict, Union

from interpreter.error import UsingUndefinedLabelException, MissingInstructionArgException, \
    InvalidInstructionArgumentValueException, DuplicateLabelException


class Program:
    """Entity representation of interpreted program"""

    def __init__(self, unsorted_instructions: Dict[int, 'Instruction']):
        """
        Class constructor

        :param unsorted_instructions: Dictionary of instructions
        :raise InvalidInstructionArgumentValueException: Invalid argument value
        :raise DuplicateLabelException: Duplicate labels
        """
        self.__prepare_instructions(unsorted_instructions)
        self.__create_label_dict()

    def __prepare_instructions(self, unsorted_instructions: Dict[int, 'Instruction']):
        """
        Prepares instructions from unsorted form stored in dictionary

        :param unsorted_instructions: Instructions stored like: "order: Instruction" in dictionary
        """
        self.__instructions = [unsorted_instructions[order] for order in sorted(unsorted_instructions.keys())]

    def __create_label_dict(self):
        """
        Creates a dictionary of labels

        :raise InvalidInstructionArgumentValueException: Invalid argument value
        :raise DuplicateLabelException: Duplicate labels
        """
        # Check uniqueness of label names
        unique_label_names = {
            instruction.args[0].value for instruction in self.__instructions if instruction.op_code == OpCode.LABEL
        }
        all_label_names = [
            instruction.args[0].value for instruction in self.__instructions if instruction.op_code == OpCode.LABEL
        ]

        if len(unique_label_names) != len(all_label_names):
            raise DuplicateLabelException("Program contains some duplicate labels")

        # Create groups {label_name: position_in_instructions_list}
        self.__labels = {
            instruction.args[0].value: position
            for position, instruction in enumerate(self.__instructions)
            if instruction.op_code == OpCode.LABEL
        }

    def get_instruction_at(self, position) -> 'Instruction':
        """
        Returns instruction at wanted position

        :param position: Wanted position (something like program counter value)
        :return: Instruction at wanted position
        :raise EndOfProgram: There is no instruction at wanted position, programs has already ended
        """
        if position < len(self.__instructions):
            return self.__instructions[position]
        else:
            raise EndOfProgram("No instruction at given position. Program has already ended")

    def get_jump_target(self, label: str) -> int:
        """
        Finds the target position of jump instruction (position of label the jump is onto)

        :param label: Name of label where to jump to
        :return: Position of the label in the code (position of instruction in instruction list)
        :raise UsingUndefinedLabelException: Label is undefined in the program
        """
        if label not in self.__labels:
            raise UsingUndefinedLabelException("Label isn't available")

        return self.__labels[label]


class Instruction:
    """Entity class representation of single program instruction"""

    def __init__(self, op_code: 'OpCode', args: Dict[int, 'Argument'] = None):
        """
        Class constructor

        :param op_code: Instruction's operation code
        :param args: Arguments for instruction
        """
        if args is None:
            args = {}

        self.__op_code = op_code
        self.__args = args

    @property
    def op_code(self) -> 'OpCode':
        """
        Getter for operation code

        :return: Instruction's operation code
        """
        return self.__op_code

    @property
    def args(self) -> Dict[int, 'Argument']:
        """
        Getter for arguments

        :return: Arguments of the instruction
        """
        return self.__args

    def get_arg(self, number: int) -> 'Argument':
        """
        Returns argument with specified number

        :param number: Number of needed argument
        :return: Needed argument
        :raise MissingInstructionArgException: Needed argument is missing
        """
        if number not in self.__args:
            raise MissingInstructionArgException("Needed argument is missing")

        return self.__args[number]


class Argument:
    """Entity class representation of an instruction argument"""

    def __init__(self, arg_type: 'ArgType', value: str):
        """
        Class constructor

        :param arg_type: Type of instruction argument
        :param value: Value of the argument in string form
        """
        self.__arg_type = arg_type
        self.__value = value

        self.__string_regex = re.compile("\\\\(\\d{3})")

    @property
    def arg_type(self) -> 'ArgType':
        """
        Getter for argument type

        :return: Argument type
        """
        return self.__arg_type

    @property
    def value(self) -> Union[int, str, bool, None]:
        """
        Getter for argument value

        :return: Argument value
        :raise InvalidInstructionArgumentValueException: Invalid value
        """
        if self.__arg_type == ArgType.INT:
            try:
                return int(self.__value)
            except ValueError:
                raise InvalidInstructionArgumentValueException("Invalid integer value of instruction argument")
        elif self.__arg_type == ArgType.BOOL:
            return self.__value.lower() == "true"
        elif self.__arg_type == ArgType.NIL:
            return None
        elif self.__arg_type == ArgType.STRING:
            # Convert \XXX escape sequences to characters
            # Inspired by: https://stackoverflow.com/a/18737964
            return self.__string_regex.sub(lambda match: chr(int(match.group(1))), self.__value)
        else:
            # In all other cases value is of string data type
            return self.__value


class ArgType(Enum):
    """Data types of instruction argument"""

    INT = "int"
    """Integer"""
    BOOL = "bool"
    """Boolean"""
    STRING = "string"
    """String"""
    NIL = "nil"
    """Nil (empty value)"""
    LABEL = "label"
    """Label (for jump instructions)"""
    TYPE = "type"
    """Data type name"""
    VAR = "var"
    """Variable name"""


class OpCode(Enum):
    """Supported instruction operation codes"""

    MOVE = "MOVE"
    """Syntax: MOVE <var> <symb>"""
    CREATEFRAME = "CREATEFRAME"
    """Syntax: CREATEFRAME"""
    PUSHFRAME = "PUSHFRAME"
    """Syntax: PUSHFRAME"""
    POPFRAME = "POPFRAME"
    """Syntax: POPFRAME"""
    DEFVAR = "DEFVAR"
    """Syntax: DEFVAR <var>"""
    CALL = "CALL"
    """Syntax: CALL <label>"""
    RETURN = "RETURN"
    """Syntax: RETURN"""
    PUSHS = "PUSHS"
    """Syntax: PUSHS <symb>"""
    POPS = "POPS"
    """Syntax: POPS <var>"""
    ADD = "ADD"
    """Syntax: ADD <var> <symb1> <symb2>"""
    SUB = "SUB"
    """Syntax: SUB <var> <symb1> <symb2>"""
    MUL = "MUL"
    """Syntax: MUL <var> <symb1> <symb2>"""
    IDIV = "IDIV"
    """Syntax: IDIV <var> <symb1> <symb2>"""
    LT = "LT"
    """Syntax: LT <var> <symb1> <symb2>"""
    GT = "GT"
    """Syntax: GT <var> <symb1> <symb2>"""
    EQ = "EQ"
    """Syntax: EQ <var> <symb1> <symb2>"""
    AND = "AND"
    """Syntax: AND <var> <symb1> <symb2>"""
    OR = "OR"
    """Syntax: OR <var> <symb1> <symb2>"""
    NOT = "NOT"
    """Syntax: NOT <var> <symb1> <symb2>"""
    INT2CHAR = "INT2CHAR"
    """Syntax: INT2CHAR <var> <symb>"""
    STRI2INT = "STRI2INT"
    """Syntax: STRI2INT <var> <symb1> <symb2>"""
    READ = "READ"
    """Syntax: READ <var> <type>"""
    WRITE = "WRITE"
    """Syntax: WRITE <symb>"""
    CONCAT = "CONCAT"
    """Syntax: CONCAT <var> <symb1> <symb2>"""
    STRLEN = "STRLEN"
    """Syntax: STRLEN <var> <symb>"""
    GETCHAR = "GETCHAR"
    """Syntax: GETCHAR <var> <symb1> <symb2>"""
    SETCHAR = "SETCHAR"
    """Syntax: SETCHAR <var> <symb1> <symb2>"""
    TYPE = "TYPE"
    """Syntax: TYPE <var> <symb>"""
    LABEL = "LABEL"
    """Syntax: LABEL <label>"""
    JUMP = "JUMP"
    """Syntax: JUMP <label>"""
    JUMPIFEQ = "JUMPIFEQ"
    """Syntax: JUMPIFEQ <label> <symb1> <symb2>"""
    JUMPIFNEQ = "JUMPIFNEQ"
    """Syntax: JUMPIFNEQ <label> <symb1> <symb2>"""
    EXIT = "EXIT"
    """Syntax: EXIT <symb>"""
    DPRINT = "DPRINT"
    """Syntax: DPRINT <symb>"""
    BREAK = "BREAK"
    """Syntax: BREAK"""


class EndOfProgram(Exception):
    """Exception for encountering the end of the program, so no instruction is available at given index"""
    pass
