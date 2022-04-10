<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Tools;

use Test\Exceptions\InternalErrorException;

/**
 * Manager of temporary directory (uses singleton)
 */
class TmpManager
{

    /**
     * @var string Directory for storing temporary files
     */
    private string $tmpDir;
    /**
     * @var bool Could be temporary directory cleaned at the end?
     */
    private bool $clean;

    /**
     * Private class constructor needed by singleton
     *
     * @param bool $clean Could be temporary directory cleaned at the end?
     *
     * @throws InternalErrorException Cannot create temporary directory due to name collisions
     */
    private function __construct(bool $clean)
    {
        $this->clean = $clean;

        $this->createTmpDir();
    }

    /**
     * Private clone method needed by singleton
     *
     * @return void
     */
    private function __clone(): void {}

    /**
     * Class destructor
     */
    public function __destruct()
    {
        // Clean temporary directory if it should be cleaned
        if($this->clean) {
            system("rm -rf $this->tmpDir");
        }
    }

    /**
     * Singleton method for getting an instance of this class
     *
     * @param bool $clean Could be temporary directory cleaned at the end?
     *
     * @return TmpManager Instance of this class
     * @throws InternalErrorException Cannot create temporary directory due to name collisions
     */
    public static function getInstance(bool $clean): TmpManager
    {
        return new TmpManager($clean);
    }

    /**
     * Creates temporary directory for help files, etc.
     *
     * @throws InternalErrorException Cannot create directory
     */
    private function createTmpDir(): void
    {
        $tmpDirName = posix_getcwd() . '/tmp';

        // Directory can't exist
        if(file_exists($tmpDirName)) {
            $tmpDirName .= '-' . date('U');
        }

        // Adding UNIX epoch didn't solve the problem --> there is no automatic solution
        if(file_exists($tmpDirName)) {
            throw new InternalErrorException('Unable to create temporary directory due to unavailable name.');
        }

        mkdir($tmpDirName);

        $this->tmpDir = realpath($tmpDirName);
    }

    /**
     * Getter for temporary directory
     *
     * @return string Path to the temporary directory (absolute, with realpath() applied)
     */
    public function getTmpDir(): string
    {
        return $this->tmpDir;
    }
}
