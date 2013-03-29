<?

namespace Appcia\Webwork\System;

use Appcia\Webwork\Exception;

class File
{
    /**
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $path Path
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
        if (!unlink($this->path)) {
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
        if (!touch($this->path)) {
            throw new Exception(sprintf(
                "Cannot create a file: '%s'" . PHP_EOL
                    . 'Verify access permissions', $this->path
            ));
        }

        return $this;
    }

    /**
     * Move file to another directory
     *
     * @param Dir|string $dir Target directory
     *
     * @return File
     * @throws Exception
     */
    public function move($dir)
    {
        if (!$dir instanceof Dir) {
            $dir = new Dir($dir);
        }

        $targetPath = $dir->getPath($this->getBaseName());

        if (!rename($this->path, $targetPath)) {
            throw new Exception(sprintf("Cannot move a file to directory: %s -> %s" . PHP_EOL
                . 'Verify access permissions', $this->path, $targetPath));
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

        if ($link->isLink() && !unlink($file)) {
            throw new Exception(sprintf("Cannot remove an existing link: '%s'", $file));
        }

        if (!symlink($this->getAbsolutePath(), $link->getAbsolutePath())) {
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

        $data = file_get_contents($this->path);

        if ($data === false) {
            throw new Exception(sprintf("Cannot read from file: '%s'", $this->path));
        }

        return $data;
    }

    /**
     * Write data to file
     *
     * @param mixed $data     Data
     * @param bool $overwrite Overwrite file if it does not exist
     *
     * @return File
     * @throws Exception
     */
    public function write($data, $overwrite = true)
    {
        if (!$overwrite && $this->exists()) {
            throw new Exception(sprintf("Cannot overwrite file. File already exists: '%s'", $this->path));
        }

        if (!file_put_contents($this->path, $data)) {
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

        $bytes = file_put_contents($this->path, $data, FILE_APPEND);

        if ($bytes === false) {
            throw new Exception(sprintf("Cannot append data to file: '%s'" . PHP_EOL
                . 'Verify access permissions', $this->path));
        }

        return $this;
    }
}