<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Tools;

/**
 * Object representation of A7Soft JExamXML program
 */
class JExamXmlDiff implements DiffProgram
{

    /**
     * Name of the JAR file of the JExamXML program
     */
    private const JAR_FILE = "jexamxml.jar";
    /**
     * Name of the options file for the JExamXML program
     */
    private const OPTIONS_FILE = "options";

    /**
     * @var string Path to the folder with JExamXml JAR file and config files
     */
    private string $pathToJExamXml;
    /**
     * @var string Path to directory where to store temporary help files
     */
    private string $tmpDir;

    /**
     * Class constructor
     *
     * @param string $pathToJExamXml Path to the folder with JExamXml JAR file and config files
     * @param string $tmpDir Path to directory where to store temporary help files
     */
    public function __construct(string $pathToJExamXml, string $tmpDir)
    {
        $this->pathToJExamXml = rtrim($pathToJExamXml, '/');
        $this->tmpDir = rtrim($tmpDir, '/');
    }

    /**
     * Checks if provided files are different or the same
     *
     * @param string $firstFile First file to compare
     * @param string $secondFile Second file to compare
     * @param string|null $deltaFile File where to save difference (will be overwritten)
     *
     * @return bool Are contents of these files the same?
     */
    public function fileDiff(string $firstFile, string $secondFile, ?string $deltaFile = null): bool
    {
        $output = null;
        $exitCode = null;
        $program = "$this->pathToJExamXml/" . self::JAR_FILE;
        $options = "$this->pathToJExamXml/" . self::OPTIONS_FILE;

        if($deltaFile == null) {
            // Delta file name is derived from the name of the first file
            $firstFileParts = explode('/', $firstFile);
            $deltaFile = "$this->tmpDir/" . array_pop($firstFileParts) . ".delta";
        }

        exec("java -jar $program $firstFile $secondFile $deltaFile $options", $output, $exitCode);

        return $exitCode == 0;
    }
}
