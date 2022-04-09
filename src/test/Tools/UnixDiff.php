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
 * Object interface of UNIX diff program
 */
class UnixDiff implements DiffProgram
{

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

        exec("diff $firstFile $secondFile", $output, $exitCode);

        if($deltaFile != null) {
            file_put_contents($deltaFile, $output);
        }

        return $exitCode == 0;
    }
}
