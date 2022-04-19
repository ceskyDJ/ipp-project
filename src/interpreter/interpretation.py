# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

import re
import sys
from sys import stdin
from typing import Optional, Dict, NoReturn, List, Tuple, Union
from xml.etree.ElementTree import ElementTree, ParseError

from interpreter.error import BadInstructionOrderException, BadXmlStructureException, XmlParsingErrorException, \
    MissingInstructionArgException, InvalidDataTypeException, TooFewInstructionArgsException, ZeroDivisionException, \
    ExitValueOutOfRangeException, InvalidAsciiPositionException, IndexingOutsideStringException, \
    InvalidInstructionOpCode
from interpreter.code import Program, Instruction, OpCode, Argument, ArgType, EndOfProgram
from interpreter.memory import ProcessMemory, CallStack, DataStack, DataType, Value


class Interpreter:
    """Controller of the interpretation process"""

    def __init__(self, input_file: Optional[str]):
        """
        Class constructor

        :param input_file: Path to file with inputs for interpretation or None for stdin
        """
        self.__program: Optional[Program] = None
        self.__input_file = input_file

        self.__program_counter = 0
        self.__memory = ProcessMemory()
        self.__call_stack = CallStack()
        self.__data_stack = DataStack()

    def run(self, program: Program) -> None:
        """
        Runs interpretation

        :param program: Object representation of the program for interpretation
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise ZeroDivisionException: Zero division
        :raise ExitValueOutOfRangeException: Exit code out of range
        :raise UsingUndefinedLabelException: Label is undefined in the program
        :raise PopEmptyStackException: Popping from an empty call stack
        :raise InvalidAsciiPositionException: Converting non-ASCII position to char
        :raise IndexingOutsideStringException: Indexing outside string
        :raise VariableRedefinitionException: Already defined variable
        """
        self.__program = program

        # Hack allowing always use import()
        # Source: https://stackoverflow.com/a/69154316
        if self.__input_file is not None:
            # Backup stdin
            sys_stdin_backup = sys.stdin

            # Change Pythons handle to standard input file
            sys.stdin = open(self.__input_file)

        # Interpretation process
        try:
            while True:
                instruction = self.__program.get_instruction_at(self.__program_counter)

                self.__execute(instruction)
        except EndOfProgram:
            # End of program --> end with interpretation
            pass

        # Revert changes by hack
        if self.__input_file is not None:
            # Close handle of the own "stdin" file
            sys.stdin.close()

            # Revert stdin (it is changed only if input file isn't None, which means stdin)
            # noinspection PyUnboundLocalVariable
            sys.stdin = sys_stdin_backup

    def __execute(self, instruction: Instruction) -> None:
        """
        Executes an instruction

        :param instruction: Instruction to execute
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise ZeroDivisionException: Zero division
        :raise ExitValueOutOfRangeException: Exit code out of range
        :raise UsingUndefinedLabelException: Label is undefined in the program
        :raise PopEmptyStackException: Popping from an empty call stack
        :raise InvalidAsciiPositionException: Converting non-ASCII position to char
        :raise IndexingOutsideStringException: Indexing outside string
        :raise VariableRedefinitionException: Already defined variable
        """
        if instruction.op_code == OpCode.MOVE:
            self.__move(instruction.args)
        elif instruction.op_code == OpCode.CREATEFRAME:
            self.__create_frame(instruction.args)
        elif instruction.op_code == OpCode.PUSHFRAME:
            self.__push_frame(instruction.args)
        elif instruction.op_code == OpCode.POPFRAME:
            self.__pop_frame(instruction.args)
        elif instruction.op_code == OpCode.DEFVAR:
            self.__defvar(instruction.args)
        elif instruction.op_code == OpCode.CALL:
            self.__call(instruction.args)
        elif instruction.op_code == OpCode.RETURN:
            self.__return(instruction.args)
        elif instruction.op_code == OpCode.PUSHS:
            self.__pushs(instruction.args)
        elif instruction.op_code == OpCode.POPS:
            self.__pops(instruction.args)
        elif instruction.op_code == OpCode.ADD:
            self.__add(instruction.args)
        elif instruction.op_code == OpCode.SUB:
            self.__sub(instruction.args)
        elif instruction.op_code == OpCode.MUL:
            self.__mul(instruction.args)
        elif instruction.op_code == OpCode.IDIV:
            self.__idiv(instruction.args)
        elif instruction.op_code == OpCode.LT:
            self.__lt(instruction.args)
        elif instruction.op_code == OpCode.GT:
            self.__gt(instruction.args)
        elif instruction.op_code == OpCode.EQ:
            self.__eq(instruction.args)
        elif instruction.op_code == OpCode.AND:
            self.__and(instruction.args)
        elif instruction.op_code == OpCode.OR:
            self.__or(instruction.args)
        elif instruction.op_code == OpCode.NOT:
            self.__not(instruction.args)
        elif instruction.op_code == OpCode.INT2CHAR:
            self.__int2char(instruction.args)
        elif instruction.op_code == OpCode.STRI2INT:
            self.__stri2int(instruction.args)
        elif instruction.op_code == OpCode.READ:
            self.__read(instruction.args)
        elif instruction.op_code == OpCode.WRITE:
            self.__write(instruction.args)
        elif instruction.op_code == OpCode.CONCAT:
            self.__concat(instruction.args)
        elif instruction.op_code == OpCode.STRLEN:
            self.__strlen(instruction.args)
        elif instruction.op_code == OpCode.GETCHAR:
            self.__get_char(instruction.args)
        elif instruction.op_code == OpCode.SETCHAR:
            self.__set_char(instruction.args)
        elif instruction.op_code == OpCode.TYPE:
            self.__type(instruction.args)
        elif instruction.op_code == OpCode.LABEL:
            self.__label(instruction.args)
        elif instruction.op_code == OpCode.JUMP:
            self.__jump(instruction.args)
        elif instruction.op_code == OpCode.JUMPIFEQ:
            self.__jump_if_eq(instruction.args)
        elif instruction.op_code == OpCode.JUMPIFNEQ:
            self.__jump_if_neq(instruction.args)
        elif instruction.op_code == OpCode.EXIT:
            self.__exit(instruction.args)
        elif instruction.op_code == OpCode.DPRINT:
            self.__dprint(instruction.args)
        elif instruction.op_code == OpCode.BREAK:
            self.__break(instruction.args)

        # Increment program counter
        self.__program_counter += 1

    def __check_data_types(self, pattern: List[Union[ArgType, Tuple[ArgType, ...]]], args: Dict[int, Argument]) -> None:
        """
        Checks data types of instruction operands

        :param pattern: Pattern to check by
        :param args: Arguments to test
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        if len(args) > len(pattern):
            raise TooFewInstructionArgsException(f"Instruction wants {len(pattern)} args but got {len(args)}")

        for arg_number, ref_types in enumerate(pattern):
            if arg_number not in args:
                raise MissingInstructionArgException("Some instruction argument is missing")

            # If only one reference type in available, convert it to one-item tuple
            if type(ref_types) != tuple:
                ref_types = (ref_types, )

            for ref_type_number, ref_type in enumerate(ref_types):
                try:
                    arg_type = args[arg_number].arg_type
                    if arg_type != ArgType.VAR and ref_type != ArgType.VAR:
                        # Value <-> Value
                        # Types can be tested directly
                        if arg_type != ref_type:
                            raise InvalidDataTypeException("Invalid data type of instruction operand")
                    elif arg_type == ArgType.VAR and ref_type != ArgType.VAR:
                        # Var <-> Value
                        # This is possible only for int, bool, string and nil types, otherwise it is an error
                        if ref_type not in [ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL]:
                            raise InvalidDataTypeException("Invalid data type of instruction operand")

                        # Types must be tested indirectly from variable's value
                        variable = self.__memory.get_variable(args[arg_number].value)
                        var_value = variable.value
                        if ArgType(var_value.val_type.value) != ref_type:
                            raise InvalidDataTypeException("Invalid data type of variable in instruction operand")
                    elif arg_type != ArgType.VAR and ref_type == ArgType.VAR:
                        # Value <-> Var
                        # Variable is needed (writing must be supported) but the operand is a value
                        raise InvalidDataTypeException("Variable is needed, value has been set as operand")
                    else:
                        # Var <-> Var
                        # It will be always OK (Var is elementary type here - we need some variable, we got it)
                        pass

                    # Type of argument fits some reference type
                    break
                except Exception as e:
                    # If it was the last possible reference type --> it cant fit
                    if (ref_type_number + 1) == len(ref_types):
                        raise e

    def __get_value_from_arg(self, argument: Argument) -> Tuple[DataType, Union[int, str, bool, None]]:
        """
        Extracts value from instruction argument

        :param argument: Instruction argument
        :return: Extracted value (truly typed)
        """
        if argument.arg_type == ArgType.INT:
            return DataType.INT, int(argument.value)
        elif argument.arg_type == ArgType.BOOL:
            return DataType.BOOL, bool(argument.value.lower() == "true")
        elif argument.arg_type == ArgType.STRING:
            return DataType.STRING, str(argument.value)
        elif argument.arg_type == ArgType.NIL:
            return DataType.NIL, None
        else:
            # Variable in argument --> need to be read from memory
            variable = self.__memory.get_variable(argument.value)
            value = variable.value

            return value.val_type, value.content

    def __move(self, args: Dict[int, Argument]) -> None:
        """
        Copies value into variable

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        variable = self.__memory.get_variable(args[0].value)
        data_type, raw_value = self.__get_value_from_arg(args[1])

        var_value = Value(data_type, raw_value)
        variable.value = var_value

    def __create_frame(self, args: Dict[int, Argument]) -> None:
        """
        Creates a temporary memory frame

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([], args)

        self.__memory.create_frame()

    def __push_frame(self, args: Dict[int, Argument]) -> None:
        """
        Pushes temporary memory frame to the local memory frame stack

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([], args)

        self.__memory.push_frame()

    def __pop_frame(self, args: Dict[int, Argument]) -> None:
        """
        Moves memory frame on the top of the local memory frame stack to temporary frame

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([], args)

        self.__memory.pop_frame()

    def __defvar(self, args: Dict[int, Argument]) -> None:
        """
        Defines a new variable (not initialized)

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise VariableRedefinitionException: Already defined variable
        """
        self.__check_data_types([ArgType.VAR], args)

        self.__memory.define_variable(args[0].value)

    def __call(self, args: Dict[int, Argument]) -> None:
        """
        Calls a function

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise UsingUndefinedLabelException: Label is undefined in the program
        """
        self.__check_data_types([ArgType.LABEL], args)

        # Add current position + 1 to call stack
        self.__call_stack.push(self.__program_counter + 1)

        # Jump to instruction after label
        label_position = self.__program.get_jump_target(args[0].value)
        self.__program_counter = label_position + 1

    def __return(self, args: Dict[int, Argument]) -> None:
        """
        Returns from a function to the place where it was called from + 1 instruction

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise PopEmptyStackException: Popping from an empty call stack
        """
        self.__check_data_types([], args)

        # Get position from call stack
        new_position = self.__call_stack.pop()

        # Jump to recovered position
        self.__program_counter = new_position

    def __pushs(self, args: Dict[int, Argument]) -> None:
        """
        Pushes a value to the data stack

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([(ArgType.VAR, ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        data_type, raw_value = self.__get_value_from_arg(args[0])

        obj_value = Value(data_type, raw_value)
        self.__data_stack.push(obj_value)

    def __pops(self, args: Dict[int, Argument]) -> None:
        """
        Returns and removes value from the top of the data stack

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise PopEmptyStackException: Popping from an empty data stack
        """
        self.__check_data_types([ArgType.VAR], args)

        variable = self.__memory.get_variable(args[0].value)

        value = self.__data_stack.pop()
        variable.value = value

    def __add(self, args: Dict[int, Argument]) -> None:
        """
        Counts addition

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT, ArgType.INT], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.INT, first + second)
        variable.value = result

    def __sub(self, args: Dict[int, Argument]) -> None:
        """
        Counts subtraction

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT, ArgType.INT], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.INT, first - second)
        variable.value = result

    def __mul(self, args: Dict[int, Argument]) -> None:
        """
        Counts multiplication

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT, ArgType.INT], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.INT, first * second)
        variable.value = result

    def __idiv(self, args: Dict[int, Argument]) -> None:
        """
        Counts integer division

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise ZeroDivisionException: Zero division
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT, ArgType.INT], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        if second == 0:
            raise ZeroDivisionException("Division with zero constant is forbidden")

        result = Value(DataType.INT, first // second)
        variable.value = result

    def __lt(self, args: Dict[int, Argument]) -> None:
        """
        Compares if the left value is less than right value

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, (ArgType.INT, ArgType.BOOL, ArgType.STRING),
                                 (ArgType.INT, ArgType.BOOL, ArgType.STRING)], args)

        if args[1].arg_type != args[2].arg_type:
            raise InvalidDataTypeException("Both operand must be of the same type")

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, first < second)
        variable.value = result

    def __gt(self, args: Dict[int, Argument]) -> None:
        """
        Compares if the left value is greater than right value

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, (ArgType.INT, ArgType.BOOL, ArgType.STRING),
                                 (ArgType.INT, ArgType.BOOL, ArgType.STRING)], args)

        if args[1].arg_type != args[2].arg_type:
            raise InvalidDataTypeException("Both operand must be of the same type")

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, first > second)
        variable.value = result

    def __eq(self, args: Dict[int, Argument]) -> None:
        """
        Compares if the left and the right values are equal

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL),
                                 (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        if args[1].arg_type != args[2].arg_type and args[1].arg_type != ArgType.NIL and args[2].arg_type != ArgType.NIL:
            raise InvalidDataTypeException("Both operand must be of the same type or one of them could be of type nil")

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, first == second)
        variable.value = result

    def __and(self, args: Dict[int, Argument]) -> None:
        """
        Does logical AND

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.BOOL, ArgType.BOOL], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, first and second)
        variable.value = result

    def __or(self, args: Dict[int, Argument]) -> None:
        """
        Does logical OR

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.BOOL, ArgType.BOOL], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, first or second)
        variable.value = result

    def __not(self, args: Dict[int, Argument]) -> None:
        """
        Does logical NOT

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.BOOL], args)

        _, value = self.__get_value_from_arg(args[1])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.BOOL, not value)
        variable.value = result

    def __int2char(self, args: Dict[int, Argument]) -> None:
        """
        Converts ASCII integer value to character

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise InvalidAsciiPositionException: Converting non-ASCII position to char
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT], args)

        _, value = self.__get_value_from_arg(args[1])
        variable = self.__memory.get_variable(args[0].value)

        try:
            result = Value(DataType.STRING, chr(value))
            variable.value = result
        except ValueError:
            raise InvalidAsciiPositionException("Non-ASCII position cannot be converted to char with ASCII")

    def __stri2int(self, args: Dict[int, Argument]) -> None:
        """
        Converts some character from a string to its ASCII integer value

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise IndexingOutsideStringException: Indexing outside string
        """
        self.__check_data_types([ArgType.VAR, ArgType.STRING, ArgType.INT], args)

        _, string = self.__get_value_from_arg(args[1])
        _, position = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        if position > len(string):
            raise IndexingOutsideStringException("Indexing outside the last char of the string")

        result = Value(DataType.STRING, ord(string))
        variable.value = result

    def __read(self, args: Dict[int, Argument]) -> None:
        """
        Reads value from standard input

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.TYPE], args)

        _, type_for_loading = self.__get_value_from_arg(args[1])
        variable = self.__memory.get_variable(args[0].value)

        try:
            loaded_value = input()

            if type_for_loading == "int":
                raw_value = int(loaded_value)
            elif type_for_loading == "bool":
                raw_value = loaded_value.lower() == "true"
            else:  # type_for_loading == "string"
                raw_value = loaded_value

            data_type = DataType(type_for_loading)
        except EOFError:
            data_type = DataType.NIL
            raw_value = None

        var_value = Value(data_type, raw_value)
        variable.value = var_value

    def __write(self, args: Dict[int, Argument]) -> None:
        """
        Writes value to the standatd output

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([(ArgType.VAR, ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        data_type, value = self.__get_value_from_arg(args[0])

        if data_type == DataType.BOOL:
            print("true" if value else "false", end="")
        elif data_type == DataType.NIL:
            print("", end="")
        else:
            print(value, end="")

    def __concat(self, args: Dict[int, Argument]) -> None:
        """
        Concatenates two strings

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.STRING, ArgType.STRING], args)

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.STRING, first + second)
        variable.value = result

    def __strlen(self, args: Dict[int, Argument]) -> None:
        """
        Counts length of a string

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.STRING], args)

        _, value = self.__get_value_from_arg(args[1])
        variable = self.__memory.get_variable(args[0].value)

        result = Value(DataType.INT, len(value))
        variable.value = result

    def __get_char(self, args: Dict[int, Argument]) -> None:
        """
        Returns a character at some position of a string

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise IndexingOutsideStringException: Indexing outside string
        """
        self.__check_data_types([ArgType.VAR, ArgType.STRING, ArgType.INT], args)

        _, string = self.__get_value_from_arg(args[1])
        _, position = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        if position > len(string):
            raise IndexingOutsideStringException("Indexing outside the last char of the string")

        result = Value(DataType.STRING, string[position])
        variable.value = result

    def __set_char(self, args: Dict[int, Argument]) -> None:
        """
        Modifies character at some position of a string

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, ArgType.INT, ArgType.STRING], args)

        _, position = self.__get_value_from_arg(args[1])
        _, new_char = self.__get_value_from_arg(args[2])
        variable = self.__memory.get_variable(args[0].value)

        var_value = variable.value
        value_content: str = var_value.content

        if position > len(value_content):
            raise IndexingOutsideStringException("Indexing outside the last char of the string")

        result = Value(DataType.STRING, value_content[:position] + new_char + value_content[position + 1:])
        variable.value = result

    def __type(self, args: Dict[int, Argument]) -> None:
        """
        Gets a type of the value

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.VAR, (ArgType.VAR, ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)],
                                args)

        data_type, _ = self.__get_value_from_arg(args[1])
        variable = self.__memory.get_variable(args[0].value)

        value = Value(DataType.STRING, data_type.value)
        variable.value = value

    def __label(self, args: Dict[int, Argument]) -> None:
        """
        Add a label for jump/call instructions

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.LABEL], args)

        # This instruction does really nothing, it is for naming addresses
        # This usage has been used by interpreter in loading phase yet

    def __jump(self, args: Dict[int, Argument]) -> None:
        """
        Jumps to a label (unconditionally)

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.LABEL], args)

        self.__program_counter = self.__program.get_jump_target(args[0].value)

    def __jump_if_eq(self, args: Dict[int, Argument]) -> None:
        """
        Conditionally jumps to a label if values are equal

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.LABEL, (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL),
                                 (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        if args[1].arg_type != args[2].arg_type and args[1].arg_type != ArgType.NIL and args[2].arg_type != ArgType.NIL:
            raise InvalidDataTypeException("Both operand must be of the same type or one of them could be of type nil")

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])

        if first == second:
            self.__program_counter = self.__program.get_jump_target(args[0].value)

    def __jump_if_neq(self, args: Dict[int, Argument]) -> None:
        """
        Conditionally jumps to a label if values are NOT equal

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([ArgType.LABEL, (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL),
                                 (ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        if args[1].arg_type != args[2].arg_type and args[1].arg_type != ArgType.NIL and args[2].arg_type != ArgType.NIL:
            raise InvalidDataTypeException("Both operand must be of the same type or one of them could be of type nil")

        _, first = self.__get_value_from_arg(args[1])
        _, second = self.__get_value_from_arg(args[2])

        if first != second:
            self.__program_counter = self.__program.get_jump_target(args[0].value)

    def __exit(self, args: Dict[int, Argument]) -> NoReturn:
        """
        Stops interpretation with an exit code

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        :raise ExitValueOutOfRangeException: Exit code out of range
        """
        self.__check_data_types([ArgType.INT], args)

        _, value = self.__get_value_from_arg(args[0])

        if value < 0 or value > 49:
            raise ExitValueOutOfRangeException(f"Exit value {value} is out of range <0, 49>")

        exit(value)

    def __dprint(self, args: Dict[int, Argument]) -> None:
        """
        Writes a value to the standard error output

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([(ArgType.VAR, ArgType.INT, ArgType.BOOL, ArgType.STRING, ArgType.NIL)], args)

        data_type, value = self.__get_value_from_arg(args[0])

        # For better readability...
        if data_type == DataType.NIL:
            value = "nil"
        elif data_type == DataType.BOOL:
            value = "true" if value else "false"

        if args[0].arg_type == ArgType.VAR:
            print(f"DPRINT: {args[0].value} = {data_type.value}@{value}", file=sys.stderr)
        else:
            print(f"DPRINT: {data_type.value}@{value}", file=sys.stderr)

    def __break(self, args: Dict[int, Argument]) -> None:
        """
        Writes information about interpretation to the standard error output

        :param args: Instruction arguments
        :raise InvalidDataTypeException: Invalid data type
        :raise MissingInstructionArgException: Missing argument
        :raise NonExistingVarException: Variable doesn't exist
        :raise GetValueFromNotInitVarException: Variable isn't initialized
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise TooFewInstructionArgsException: Too many arguments
        """
        self.__check_data_types([], args)

        # TODO: implement this optional instruction


class Loader:
    """Instruction loader and input verifier"""

    def __init__(self, xml_parser: ElementTree, sources_file: Optional[str]):
        """
        Class constructor

        :param xml_parser: XML parser (dependency)
        :param sources_file: Path to file where to read XML source code representation from or None for stdin
        """
        self.__xml_parser = xml_parser

        self.__sources_file = sources_file

    def load_program(self) -> Program:
        """
        Loads a program from file with its XML representation

        :return: Loaded program
        :raise BadInstructionOrderException: Duplicate or negative instruction order
        :raise BadXmlStructureException: Bad instruction location, missing attributes or values
        :raise InvalidInstructionOpCode: Invalid instruction opcode
        """
        # Set stdin if no file has been specified
        if self.__sources_file is None:
            file = stdin
        else:
            file = self.__sources_file

        # Try to parse XML
        try:
            parsed_xml = self.__xml_parser.parse(file)
        except ParseError:
            raise XmlParsingErrorException("XML isn't well-formed and couldn't been parsed")

        # Checks for root element (program)
        if parsed_xml.tag != 'program':
            raise BadXmlStructureException("Root element must be called program")
        if 'language' not in parsed_xml.attrib or parsed_xml.attrib['language'] != "IPPcode22":
            raise BadXmlStructureException("Program element must have required attribute language with value IPPcode22")

        # Prepare regular expression for extracting arguments' numbers
        extract_arg_pos_regex = re.compile("^arg(\\d+)$")

        instructions: Dict[int, Instruction] = {}
        for xml_instruction in parsed_xml:
            if xml_instruction.tag != "instruction":
                raise BadXmlStructureException("There could be only instruction elements in the program element")

            # Instruction order
            if 'order' not in xml_instruction.attrib:
                raise BadXmlStructureException("Instruction element must have required attribute order")
            try:
                order = int(xml_instruction.attrib['order'])
            except ValueError:
                raise BadInstructionOrderException("Instruction order must be valid integer value")

            if order < 0:
                raise BadInstructionOrderException("Instruction order must be positive number or zero")

            if order in instructions:
                # There mustn't be two instructions with the same order
                raise BadInstructionOrderException("Duplicate instruction order")

            # Instruction operation code
            if 'opcode' not in xml_instruction.attrib:
                raise BadXmlStructureException("Instruction element must have required attribute opcode")
            try:
                op_code = OpCode(xml_instruction.attrib['opcode'].upper())
            except ValueError:
                raise InvalidInstructionOpCode("Invalid instruction operation code")

            # Instruction arguments
            args: Dict[int, Argument] = {}
            for xml_attribute in xml_instruction:
                # Argument number
                arg_num_match = extract_arg_pos_regex.search(xml_attribute.tag)
                if arg_num_match is None:
                    raise BadXmlStructureException("There could be only argX elements in the instruction element")
                arg_num = int(arg_num_match.group(1)) - 1  # -1 => convert to numbering system that starts with 0

                if arg_num in args:
                    # Argument number must be unique within one instruction
                    raise BadXmlStructureException("Duplicate argument number")

                # Argument type
                if 'type' not in xml_attribute.attrib:
                    raise BadXmlStructureException("Attribute element must have required attribute type")
                arg_type_raw = xml_attribute.attrib['type']
                arg_type = ArgType(arg_type_raw.lower())

                # Argument value
                if xml_attribute.text is None:
                    raise BadXmlStructureException("Attribute element must contain a value")

                args[arg_num] = Argument(arg_type, str(xml_attribute.text))

            instructions[order] = Instruction(op_code, args)

        return Program(instructions)
