# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

import re
from sys import stdin
from typing import Optional, Dict
from xml.etree.ElementTree import ElementTree, ParseError

from interpreter.error import BadInstructionOrderException, BadXmlStructureException, XmlParsingErrorException
from interpreter.code import Program, Instruction, OpCode, Argument, ArgType


class Interpreter:
    """Controller of the interpretation process"""
    pass


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
        extract_arg_pos_regex = re.compile("arg([0-9]+)")

        instructions: Dict[int, Instruction] = {}
        for xml_instruction in parsed_xml:
            if xml_instruction.tag != "instruction":
                raise BadXmlStructureException("There could be only instruction elements in the program element")

            # Instruction order
            if 'order' not in xml_instruction.attrib:
                raise BadXmlStructureException("Instruction element must have required attribute order")
            order = int(xml_instruction.attrib['order'])

            # Instruction operation code
            if 'opcode' not in xml_instruction.attrib:
                raise BadXmlStructureException("Instruction element must have required attribute opcode")
            op_code = OpCode(xml_instruction.attrib['opcode'].upper())

            # There mustn't be two instructions with the same order
            if order in instructions:
                raise BadInstructionOrderException("Duplicate instruction order")

            # Instruction arguments
            args: Dict[int, Argument] = {}
            for xml_attribute in xml_instruction:
                # Argument number
                arg_num_match = extract_arg_pos_regex.search(xml_attribute.tag)
                if arg_num_match is None:
                    raise BadXmlStructureException("There could be only argX elements in the instruction element")
                arg_num = int(arg_num_match.group(1)) - 1  # -1 => convert to numbering system that starts with 0

                # Argument type
                if 'type' not in xml_attribute.attrib:
                    raise BadXmlStructureException("Attribute element must have required attribute type")
                arg_type_raw = xml_attribute.attrib['type']
                arg_type = ArgType(arg_type_raw.lower())

                if xml_attribute.text is None:
                    raise BadXmlStructureException("Attribute element must contain a value")
                args[arg_num] = Argument(arg_type, str(xml_attribute.text))

            instructions[order] = Instruction(op_code, args)

        return Program(instructions)


class Executor:
    """Instruction executor"""
    pass
