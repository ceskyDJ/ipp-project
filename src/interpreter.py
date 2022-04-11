# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022
from interpreter.error import ExitCode, InvalidInputArgException, TooManyInputArgsException, \
    MissingRequiredInputArgException, InvalidFileArgException
from interpreter.cli import CliArgParser


def main():
    """Main function controlling the running of the script"""
    # Process CLI input arguments
    try:
        cli_arg_parser = CliArgParser()
    except (InvalidInputArgException, TooManyInputArgsException, MissingRequiredInputArgException):
        exit(ExitCode.WRONG_INPUT_ARGS)
    except InvalidFileArgException:
        exit(ExitCode.INPUT_FILE_ERROR)


if __name__ == '__main__':
    main()
