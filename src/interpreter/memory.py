# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

import re
from enum import Enum
from typing import Union, Optional, Dict, List

from interpreter.error import PopEmptyStackException, EmptyLocalMemoryException, GetValueFromNotInitVarException, \
    NonExistingVarException, UsingUndefinedMemoryFrameException, VariableRedefinitionException


class ProcessMemory:
    """Abstraction of process random access memory (facade for 3 memory types)"""

    def __init__(self):
        """Class constructor"""
        # Initialize all components of process memory
        # Random access memory
        self.__global_memory_frame = MemoryFrame()
        self.__local_memory_stack = LocalMemory()
        self.__temporary_memory_frame: Optional[MemoryFrame] = None

    def __get_memory_frame(self, memory_frame_name: str) -> 'MemoryFrame':
        """
        Returns correct memory frame by its name

        :param memory_frame_name: Name of the memory frame (TF, LF, GF)
        :return: Corresponding memory frame
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Trying to access empty local memory frame stack
        """
        if memory_frame_name == "TF":
            if self.__temporary_memory_frame is None:
                raise UsingUndefinedMemoryFrameException("Using undefined memory frame")

            return self.__temporary_memory_frame
        elif memory_frame_name == "LF":

            return self.__local_memory_stack.top()
        elif memory_frame_name == "GF":
            return self.__global_memory_frame

    def get_variable(self, full_var_name: str) -> 'Variable':
        """
        Finds variable in memory

        :param full_var_name: Name of the variable (with memory frame prefix - TF@, LF@, GF@)
        :return: Found variable
        :raise NonExistingVarException: Variable doesn't exist
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        """
        regex_match = re.search("^(TF|LF|GF)@(.+)$", full_var_name)
        memory_frame_name = regex_match.group(1)
        variable_name = regex_match.group(2)

        memory_frame = self.__get_memory_frame(memory_frame_name)

        return memory_frame.get_variable(variable_name)

    def define_variable(self, full_var_name: str) -> 'Variable':
        """
        Defines new variable in memory

        :param full_var_name: Name of the variable (with memory frame prefix - TF@, LF@, GF@)
        :return: Newly defined variable
        :raise UsingUndefinedMemoryFrameException: Using undefined memory frame
        :raise EmptyLocalMemoryException: Empty local memory stack
        :raise VariableRedefinitionException: Already defined variable
        """
        regex_match = re.search("^(TF|LF|GF)@(.+)$", full_var_name)
        memory_frame_name = regex_match.group(1)
        variable_name = regex_match.group(2)

        variable = Variable(variable_name)

        memory_frame = self.__get_memory_frame(memory_frame_name)

        try:
            memory_frame.get_variable(variable.name)

            # Variable has been defined yet
            raise VariableRedefinitionException("Defining variable that has been defined yet")
        except NonExistingVarException:
            # Variable isn't in the memory --> it can be added
            memory_frame.add_variable(variable)

        return variable

    def push_frame(self) -> None:
        """
        Push temporary memory frame to the top of local memory frame stack

        :raise UsingUndefinedMemoryFrameException: Temporary memory frame is undefined
        """
        if self.__temporary_memory_frame is None:
            raise UsingUndefinedMemoryFrameException("Using undefined memory frame")

        self.__local_memory_stack.push(self.__temporary_memory_frame)
        self.__temporary_memory_frame = None

    def pop_frame(self) -> None:
        """
        Pop the most-local memory frame from local memory stack to the temporary memory frame

        :raise EmptyLocalMemoryException: Popping memory frame from empty local memory frame stack
        """
        self.__temporary_memory_frame = self.__local_memory_stack.pop()

    def create_frame(self) -> None:
        """Creates a new temporary memory frame (replaces old if needed)"""
        self.__temporary_memory_frame = MemoryFrame()


class LocalMemory:
    """Stack of memory frames"""

    def __init__(self):
        """Class constructor"""
        self.__data: List['MemoryFrame'] = []

    def push(self, memory_frame: 'MemoryFrame') -> None:
        """
        Adds memory frame to the top of the stack

        :param memory_frame: The memory frame to push
        """
        self.__data.append(memory_frame)

    def pop(self) -> 'MemoryFrame':
        """
        Returns and removes memory frame from the top of the stack

        :return: The memory frame on the top of the stack
        :raise EmptyLocalMemoryException: Popping memory frame from empty local memory frame stack
        """
        if len(self.__data) == 0:
            raise EmptyLocalMemoryException("There is no memory frame in the local memory stack. Pop is unavailable")

        return self.__data.pop()

    def top(self) -> 'MemoryFrame':
        """
        Returns memory frame on the top of the stack

        :return: The most local memory frame
        :raise EmptyLocalMemoryException: Trying to access empty local memory frame stack
        """
        if len(self.__data) == 0:
            raise EmptyLocalMemoryException("There is no memory frame in the local memory stack. Cannot add var")

        return self.__data[-1]


class MemoryFrame:
    """Single memory frame (place for storing variables) - wrapper to Python's dictionary"""

    def __init__(self):
        """Class constructor"""
        self.__data: Dict[str, 'Variable'] = {}

    def add_variable(self, variable: 'Variable') -> None:
        """
        Adds a variable to the memory frame

        :param variable: Variable to add
        """
        self.__data[variable.name] = variable

    def get_variable(self, name: str) -> 'Variable':
        """
        Returns variable stored in memory

        :param name: Name of the variable
        :return: Found variable
        :raise NonExistingVarException: Non-existing variable
        """
        if name not in self.__data:
            raise NonExistingVarException("Variable doesn't exist in the specified memory frame")

        return self.__data[name]


class Variable:
    """Representation of variable stored in memory frame"""

    def __init__(self, name: str, value: Optional['Value'] = None):
        """
        Class constructor

        :param name: Name of the variable
        :param value: Stored value (or nothing for newly created variables)
        """
        self.__name = name
        # Newly created variables has no value
        self.__value = value

    @property
    def name(self) -> str:
        """
        Getter for variable name

        :return: Variable name
        """
        return self.__name

    @property
    def value(self) -> 'Value':
        """
        Getter for stored value

        :return: Instruction's value or None if the instruction is uninitialized
        :raise GetValueFromNotInitVarException: Variable isn't initialized (has no value)
        """
        if self.__value is None:
            raise GetValueFromNotInitVarException("The variable is uninitialized. Its value can't be get")

        return self.__value

    @value.setter
    def value(self, new_value: 'Value') -> None:
        """
        Setter for stored value

        :param new_value: New value to store in the variable
        """
        self.__value = new_value


class DataStack:
    """Data stack emulator (Python list wrapper)"""

    def __init__(self):
        """Class constructor"""
        self.__data = []

    def push(self, value: 'Value') -> None:
        """
        Pushes the value to the top of the data stack

        :param value: Value to push
        """
        self.__data.append(value)

    def pop(self) -> 'Value':
        """
        Returns value on the stack top and removes it from there

        :return: Value from the top of the stack
        :raise PopEmptyStackException: Popping from an empty data stack
        """
        if len(self.__data) == 0:
            raise PopEmptyStackException("Pop from an empty stack isn't possible")

        return self.__data.pop()


class CallStack:
    """Emulation of the stack for function calls storing backed up addresses of places where the call was executed
    (Python list wrapper)"""

    def __init__(self):
        """Class constructor"""
        self.__data = []

    def push(self, memory_position: int) -> None:
        """
        Pushes the memory position where to continue after executing RETURN instruction to the top of the call stack

        :param memory_position: The memory position (in simulated program object) to push
        """
        self.__data.append(memory_position)

    def pop(self) -> int:
        """
        Returns saved memory position from the top of the call stack

        :return: The memory position from the top
        :raise PopEmptyStackException: Popping from an empty call stack
        """
        if len(self.__data) == 0:
            raise PopEmptyStackException("Pop from a call stack isn't possible now. It's empty")

        return self.__data.pop()


class Value:
    """Entity representation of dynamic-typed value"""

    def __init__(self, val_type: 'DataType', value: Union[int, bool, str, None]):
        """
        Class constructor

        :param val_type: Type of the value
        :param value: Dynamically typed value with typed specified as a first parameter
        """
        self.__val_type = val_type
        self.__value = value

    @property
    def val_type(self) -> 'DataType':
        """
        Getter for type of the value

        :return: Data type of the value
        """
        return self.__val_type

    @property
    def content(self) -> Union[int, bool, str, None]:
        """
        Getter for content

        :return: Content of type Value.val_type()
        """
        return self.__value


class DataType(Enum):
    """Available data types"""

    INT = "int"
    """Integer"""
    BOOL = "bool"
    """Boolean"""
    STRING = "string"
    """String"""
    NIL = "nil"
