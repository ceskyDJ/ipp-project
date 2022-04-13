# This is a part of IPP project
#
# Author: Michal Šmahel (xsmahe01)
# Date: 2022

import sys
import textwrap
from argparse import ArgumentParser, RawDescriptionHelpFormatter
from os import access, R_OK
from os.path import realpath, isfile
from typing import Optional

from interpreter.error import ExitCode, InvalidInputArgException, TooManyInputArgsException, \
    MissingRequiredInputArgException, InvalidFileArgException


class CliArgParser(ArgumentParser):
    """Parser of command line arguments (wrapper of argparse.ArgumentParser)"""

    def __init__(self):
        """
        Class constructor

        :raise TooManyInputArgumentsException: --help switch must be entered alone
        :raise InvalidInputArgumentException: Not known input argument was entered
        :raise MissingRequiredInputArgException: At least one of the --source and --input must be set
        :raise InvalidFileArgException: File in --source or --input isn't valid
        """
        # Initialize and configure wrapped class
        super().__init__(
            formatter_class=CzechHelpFormatter,
            description=textwrap.dedent("""\
                interpret.py je skript slouzici pro interpretaci XML reprezentace
                kodu vytvorene skriptem parse.php ze zdrojoveho kodu v jazyce
                IPPcode22. Jedna se o soucast 2. casti projektu do predmetu IPP
                na FIT VUT.
            """),
            epilog=textwrap.dedent("""\
                Vzdy musi byt zadan alespon jeden z argumentu --source a --input.
                Pokud neni nektery z nich zadan, je pro dany pripad pouzit
                standardni vstup.
            """),
            add_help=False)

        self.__setup_input_arguments()
        self.__parse_input_arguments()
        self.__check_input_arguments()

        # Show help if --help switch is active
        if self.__parsed_args.help:
            self.print_help()

            exit(ExitCode.SUCCESS)

    def __setup_input_arguments(self) -> None:
        """Sets up needed input arguments"""
        # Optional arguments
        optional_args = self.add_argument_group("Nepovinne parametry")
        optional_args.add_argument("--help", action="store_true", default=False,
                                   help=f"""Zobrazi tuto napovedu a skonci s navratovym kodem {ExitCode.SUCCESS}. Tento
                                    parametr nemuze byt kombinovan s jinymi parametry. V opacnem pripade
                                    dochazi k chybe a skript je ukoncen s navratovym kodem {ExitCode.WRONG_INPUT_ARGS}.
                                    """)
        optional_args.add_argument("--source", metavar="file", type=str, default=None,
                                   help="XML reprezentace zdrojoveho kodu bude nactena ze zadaneho souboru file.")
        optional_args.add_argument("--input", metavar="file", type=str, default=None,
                                   help="Vstupy pro interpretaci budou brany ze zadaneho souboru file.")

    def __parse_input_arguments(self) -> None:
        """Parses CLI input arguments"""
        self.__parsed_args = self.parse_args()

        # Convert paths to nice absolute representation
        if self.__parsed_args.source:
            self.__parsed_args.source = realpath(self.__parsed_args.source)
        if self.__parsed_args.input:
            self.__parsed_args.input = realpath(self.__parsed_args.input)

    def __check_input_arguments(self) -> None:
        """
        Checks validity of parsed input arguments

        :raise TooManyInputArgumentsException: --help switch must be entered alone
        :raise MissingRequiredInputArgException: At least one of the --source and --input must be set
        :raise InvalidFileArgException: File in --source or --input isn't valid
        """
        # --help must be alone
        if self.__parsed_args.help and len(sys.argv) > 1:
            raise TooManyInputArgsException("If --help switch is active, no other argument is allowed")

        # One of --source and --input must be entered
        if self.__parsed_args.source is None and self.__parsed_args.input is None:
            raise MissingRequiredInputArgException("At least one of --source and --input must be entered")

        # Check files
        source_file = self.__parsed_args.source
        if source_file and (not isfile(source_file) or not access(source_file, R_OK)):
            raise InvalidFileArgException("--source must specify valid file with read access")
        input_file = self.__parsed_args.input
        if input_file and (not isfile(input_file) or not access(input_file, R_OK)):
            raise InvalidFileArgException("--input must specify valid file with read access")

    def error(self, message: str) -> None:
        """
        Handles errors caught by ArgumentParser

        :param message: Message with error details
        :raise InvalidInputArgumentException: Not known input argument was entered
        """
        # Raise own exception
        raise InvalidInputArgException(message)

    @property
    def source(self) -> Optional[str]:
        """
        Getter for file with XML source code representation

        :return: Absolute path to the file with XML source code representation or NULL (means stdin)
        """
        return self.__parsed_args.source

    @property
    def input(self) -> Optional[str]:
        """
        Getter for file with input

        :return: Absolute path to the file with input for interpretation or NULL (means stdin)
        """
        return self.__parsed_args.input


class CzechHelpFormatter(RawDescriptionHelpFormatter):
    """
    Own formatter for modifying --help output

    Source: https://stackoverflow.com/a/35848313
    """

    def add_usage(self, usage, actions, groups, prefix=None) -> None:
        """
        Adds own usage to --help

        Types are ignored, because not necessary for this use case.

        :param usage: Ignored parameter
        :param actions: Ignored parameter
        :param groups: Ignored parameter
        :param prefix: Rewritten parameter for usage of this project
        """
        prefix = "Pouziti:\npython3.8 "

        return super(CzechHelpFormatter, self).add_usage(usage, actions, groups, prefix)
