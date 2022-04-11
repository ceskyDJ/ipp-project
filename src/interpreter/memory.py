# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

from enum import Enum
from typing import Union, Optional, Dict, List

from interpreter.error import PopEmptyStackException, PopEmptyLocalMemoryException, GetValueFromNotInitVarException, \
    NonExistingVarException


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
        :raise PopEmptyLocalMemoryException: Popping memory frame from empty local memory frame stack
        """
        if len(self.__data) < 0:
            raise PopEmptyLocalMemoryException("There is no memory frame in the local memory stack. Pop is unavailable")

        return self.__data.pop()

    def add_variable(self, variable: 'Variable') -> None:
        """
        Adds a variable to the memory frame on the top (works something like proxy)

        :param variable: Variable to add
        """
        self.__data[-1].add_variable(variable)

    def get_variable(self, name: str) -> 'Variable':
        """
        Returns variable stored in memory frame on top (works something like proxy)

        :param name: Name of the variable
        :return: Found variable
        :raise NonExistingVarException: Non-existing variable
        """
        return self.__data[-1].get_variable(name)


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
            raise GetValueFromNotInitVarException("The value is uninitialized. Its value can't be get, it has no value")

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
        if len(self.__data) < 0:
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
        if len(self.__data) < 0:
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
    def value(self) -> Union[int, bool, str, None]:
        """
        Getter for value

        :return: Value of type Value.val_type()
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
