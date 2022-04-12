# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

from xml.etree.ElementTree import ElementTree

from interpreter.interpretation import Loader, Interpreter
from interpreter.error import ExitCode, InvalidInputArgException, TooManyInputArgsException, \
    MissingRequiredInputArgException, InvalidFileArgException, BadInstructionOrderException, BadXmlStructureException
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
    except (BadInstructionOrderException, BadXmlStructureException):
        return ExitCode.BAD_XML_STRUCTURE

    # Interpretation
    try:
        interpreter.run(program)
    except Exception:
        # TODO: add all exceptions and returns
        return 1

    return ExitCode.SUCCESS


if __name__ == '__main__':
    exit(main())
