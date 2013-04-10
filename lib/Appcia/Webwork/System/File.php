<?

namespace Appcia\Webwork\System;

use Appcia\Webwork\Exception;

class File
{
    /**
     * Location path
     *
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $path Path
     *
     * @throws Exception
     */
    public function __construct($path)
    {
        if ($path === null || $path === '') {
            throw new Exception(sprintf("Invalid file path"));
        }

        $this->path = $path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get relative path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get absolute path
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return realpath($this->path);
    }

    /**
     * Get filename with extension
     *
     * @return string
     */
    public function getBaseName()
    {
        return basename($this->path);
    }

    /**
     * Get only file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Get file name without extension
     *
     * @return string
     */
    public function getName()
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Get detailed information about file
     *
     * @return array
     * @throws Exception
     */
    public function getStat()
    {
        if (!$this->exists()) {
            throw new Exception('Cannot stat a non-existing file');
        }

        $stat = @stat($this->path);

        if ($stat === false) {
            throw new Exception(sprintf("Cannot stat a file: '%s'", $this->path));
        }

        return $stat;
    }

    /**
     * Get parent directory
     *
     * @return Dir
     */
    public function getDir()
    {
        return new Dir(dirname($this->path));
    }

    /**
     * Check whether it really exists
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Check whether it is a symbolic link
     *
     * @return bool
     */
    public function isLink()
    {
        return is_link($this->path);
    }

    /**
     * Removes a file
     *
     * @return File
     * @throws Exception
     */
    public function remove()
    {
        if (!@unlink($this->path)) {
            throw new Exception(sprintf(
                "Cannot remove a file: '%s'" . PHP_EOL
                    . 'Verify access permissions', $this->path
            ));
        }

        return $this;
    }

    /**
     * Creates an empty file
     *
     * @return File
     * @throws Exception
     */
    public function create()
    {
        if (!@touch($this->path)) {
            throw new Exception(sprintf(
                "Cannot create a file: '%s'" . PHP_EOL
                    . 'Verify access permissions', $this->path
            ));
        }

        return $this;
    }

    /**
     * Move file to another location
     *
     * @param string $file       Target file
     * @param bool   $createPath Create a path to target file if does not exist
     *
     * @return File
     * @throws Exception
     */
    public function move($file, $createPath = true)
    {
        if (!$file instanceof File) {
            $file = new self($file);
        }

        if ($createPath) {
            $dir = $file->getDir();

            if (!$dir->exists()) {
                $dir->create();
            }
        }

        if (!@rename($this->path, $file->path)) {
            throw new Exception(sprintf("Cannot move a file to location: %s -> %s" . PHP_EOL
                . 'Verify access permissions', $this->path, $file->path));
        }

        return $this;
    }

    /**
     * Move file to another location
     *
     * @param string $file       Target file
     * @param bool   $createPath Create a path to target file if does not exist
     *
     * @return File
     * @throws Exception
     */
    public function copy($file, $createPath = true)
    {
        if (!$file instanceof File) {
            $file = new self($file);
        }

        if ($createPath) {
            $dir = $file->getDir();

            if (!$dir->exists()) {
                $dir->create();
            }
        }

        if (is_uploaded_file($this->path)) {
            if (!@move_uploaded_file($this->path, $file->path)) {
                throw new Exception(sprintf("Cannot move uploaded file to location: %s -> %s" . PHP_EOL
                    . 'Verify access permissions', $this->path, $file->path));
            }
        } else {
            if (!@copy($this->path, $file->path)) {
                throw new Exception(sprintf("Cannot copy a file to location: %s -> %s" . PHP_EOL
                    . 'Verify access permissions', $this->path, $file->path));
            }
        }

        return $this;
    }

    /**
     * Create a symlink pointing to this file
     *
     * @param File|string $file File object or path
     *
     * @return File
     * @throws Exception
     */
    public function symlink($file)
    {
        if (!$file instanceof self) {
            $file = new self($file);
        }

        if (!$this->exists()) {
            throw new Exception(sprintf("File does not exist: '%s'", $this->path));
        }

        $link = new self($file);

        if ($link->isLink() && !@unlink($file)) {
            throw new Exception(sprintf("Cannot remove an existing link: '%s'", $file));
        }

        if (!@symlink($this->getAbsolutePath(), $link->getAbsolutePath())) {
            throw new Exception(sprintf('Cannot create a link to file: %s -> %s', $this->getPath(), $link->getPath()));
        }

        return $this;
    }

    /**
     * Read data from file
     *
     * @return string
     * @throws Exception
     */
    public function read()
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Cannot read from non-existing file: '%s'", $this->path));
        }

        $data = @file_get_contents($this->path);

        if ($data === false) {
            throw new Exception(sprintf("Cannot read from file: '%s'", $this->path));
        }

        return $data;
    }

    /**
     * Write data to file
     *
     * @param mixed $data      Data
     * @param bool  $overwrite Overwrite file if it does not exist
     *
     * @return File
     * @throws Exception
     */
    public function write($data, $overwrite = true)
    {
        if (!$overwrite && $this->exists()) {
            throw new Exception(sprintf("Cannot overwrite file. File already exists: '%s'", $this->path));
        }

        if (!@file_put_contents($this->path, $data)) {
            throw new Exception(sprintf("Cannot write data to file: '%s'" . PHP_EOL
                . 'Verify access permissions', $this->path));
        }

        return $this;
    }

    /**
     * Append data at the end of file
     *
     * @param mixed $data Data
     *
     * @return File
     * @throws Exception
     */
    public function append($data)
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Cannot append data to non-existing file: '%s'", $this->path));
        }

        $bytes = @file_put_contents($this->path, $data, FILE_APPEND);

        if ($bytes === false) {
            throw new Exception(sprintf("Cannot append data to file: '%s'" . PHP_EOL
                . 'Verify access permissions', $this->path));
        }

        return $this;
    }

    public function equals(File $file)
    {
        $samePaths = $this->getAbsolutePath() === $file->getAbsolutePath();

        return $samePaths;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->path;
    }
}