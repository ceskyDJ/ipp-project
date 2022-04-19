# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

import traceback
from xml.etree.ElementTree import ElementTree

from interpreter.interpretation import Loader, Interpreter
from interpreter.error import ExitCode, InvalidInputArgException, TooManyInputArgsException, \
    MissingRequiredInputArgException, InvalidFileArgException, BadInstructionOrderException, BadXmlStructureException, \
    XmlParsingErrorException, InvalidDataTypeException, NonExistingVarException, GetValueFromNotInitVarException, \
    UsingUndefinedMemoryFrameException, MissingInstructionArgException, TooFewInstructionArgsException, \
    ZeroDivisionException, ExitValueOutOfRangeException, EmptyLocalMemoryException, UsingUndefinedLabelException, \
    PopEmptyStackException, InvalidAsciiPositionException, IndexingOutsideStringException, \
    VariableRedefinitionException, InvalidInstructionOpCode, InvalidInstructionArgumentValueException, \
    DuplicateLabelException
from interpreter.cli import CliArgParser


def main() -> int:
    """
    Main function controlling the running of the script

    :return: Script's exit code
    """
    # Process CLI input arguments
    try:
        cli_arg_parser = CliArgParser()
    except (InvalidInputArgException, TooManyInputArgsException, MissingRequiredInputArgException):
        return ExitCode.WRONG_INPUT_ARGS
    except InvalidFileArgException:
        return ExitCode.INPUT_FILE_ERROR

    # Needed objects
    element_tree = ElementTree()
    loader = Loader(element_tree, cli_arg_parser.source)
    interpreter = Interpreter(cli_arg_parser.input)

    # Load program
    try:
        program = loader.load_program()
    except (BadInstructionOrderException, BadXmlStructureException, InvalidInstructionOpCode):
        return ExitCode.BAD_XML_STRUCTURE
    except XmlParsingErrorException:
        return ExitCode.NOT_WELL_FORMED_XML
    except InvalidInstructionArgumentValueException:
        return ExitCode.BAD_OPERAND_VALUE
    except DuplicateLabelException:
        return ExitCode.SEMANTIC_ERROR
    except:
        traceback.print_exc()

        # For unexpected errors (primarily for debugging)
        return ExitCode.INTERNAL_ERROR

    # Interpretation
    try:
        interpreter.run(program)
    except InvalidDataTypeException:
        return ExitCode.BAD_OPERAND_TYPES
    except (MissingInstructionArgException, TooFewInstructionArgsException, InvalidInstructionArgumentValueException):
        return ExitCode.BAD_XML_STRUCTURE
    except NonExistingVarException:
        return ExitCode.NON_EXISTING_VARIABLE
    except GetValueFromNotInitVarException:
        return ExitCode.MISSING_VALUE
    except (UsingUndefinedMemoryFrameException, EmptyLocalMemoryException):
        return ExitCode.NON_EXISTING_FRAME
    except (ZeroDivisionException, ExitValueOutOfRangeException):
        return ExitCode.BAD_OPERAND_VALUE
    except UsingUndefinedLabelException:
        return ExitCode.SEMANTIC_ERROR
    except PopEmptyStackException:
        return ExitCode.MISSING_VALUE
    except (InvalidAsciiPositionException, IndexingOutsideStringException):
        return ExitCode.BAD_STRING_USAGE
    except VariableRedefinitionException:
        return ExitCode.SEMANTIC_ERROR
    except:
        # For unexpected errors (primarily for debugging)
        traceback.print_exc()

        return ExitCode.INTERNAL_ERROR

    return ExitCode.SUCCESS


if __name__ == '__main__':
    exit(main())
