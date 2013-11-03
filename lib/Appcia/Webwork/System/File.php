<?

namespace Appcia\Webwork\System;

/**
 * Filesystem file representation
 *
 * Does not necessarily refer to an existing file
 *
 * @package Appcia\Webwork\System
 */
class File
{
    const WILDCARD = '*';

    /**
     * Location path
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string $path Path
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($path)
    {
        if ($path === null || $path === '') {
            throw new \InvalidArgumentException("File is not specified");
        }

        $this->path = $path;
    }

    /**
     * Get path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
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
    public function getFileName()
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Get detailed information about file
     *
     * @return array
     * @throws \LogicException
     */
    public function getStat()
    {
        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist.", $this->path));
        }

        $stat = stat($this->path);

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
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Check whether it is a symbolic link
     *
     * @return boolean
     */
    public function isLink()
    {
        return is_link($this->path);
    }

    /**
     * Removes a file
     *
     * @return File
     */
    public function remove()
    {
        if ($this->exists()) {
            unlink($this->path);
        }

        return $this;
    }

    /**
     * Creates an empty file
     *
     * @return File
     */
    public function create()
    {
        touch($this->path);

        return $this;
    }

    /**
     * Move file to another location
     *
     * @param string $file Target file
     *
     * @return File
     */
    public function move($file)
    {
        if (!$file instanceof File) {
            $file = new self($file);
        }

        $file->getDir()
            ->create();

        rename($this->path, $file->path);

        return $this;
    }

    /**
     * Move file to another location
     *
     * @param string $file Target file
     *
     * @return File
     */
    public function copy($file)
    {
        if (!$file instanceof self) {
            $file = new self($file);
        }

        $file->getDir()
            ->create();

        if (is_uploaded_file($this->path)) {
            move_uploaded_file($this->path, $file->path);
        } else {
            copy($this->path, $file->path);
        }

        return $this;
    }

    /**
     * Create a symlink pointing to this file
     *
     * @param File|string $file File object or path
     *
     * @return File
     * @throws \LogicException
     */
    public function symlink($file)
    {
        if (!$file instanceof self) {
            $file = new self($file);
        }

        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist.", $this->path));
        }

        $link = new self($file);
        if ($link->isLink()) {
            unlink($file);
        }

        symlink($this->getAbsolutePath(), $link->getAbsolutePath());

        return $this;
    }

    /**
     * Read data from file
     *
     * @return string
     * @throws \LogicException
     * @throws \IOException
     */
    public function read()
    {
        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist.", $this->path));
        }

        $data = @file_get_contents($this->path);
        if ($data === false) {
            throw new \IOException(sprintf("Cannot read content from file '%s'.", $this->path));
        }

        return $data;
    }

    /**
     * Write data to file
     *
     * @param mixed   $content   Data
     * @param boolean $overwrite Overwrite file if it does not exist
     *
     * @return File
     * @throws \LogicException
     * @throws \IOException
     */
    public function write($content, $overwrite = true)
    {
        if (!$overwrite && $this->exists()) {
            throw new \LogicException(sprintf("File '%s' already exists so it cannot be overwritten.", $this->path));
        }

        if (!@file_put_contents($this->path, $content)) {
            throw new \IOException(sprintf("Cannot write content to file '%s'.", $this->path));
        }

        return $this;
    }

    /**
     * Append data at the end of file
     *
     * @param mixed $content Data
     *
     * @return File
     * @throws \LogicException
     * @throws \IOException
     */
    public function append($content)
    {
        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist so data cannot be appended.", $this->path));
        }

        if (!@file_put_contents($this->path, $content, FILE_APPEND)) {
            throw new \IOException(sprintf("Cannot append content to file '%s'.", $this->path));
        }

        return $this;
    }

    /**
     * Guess filename if wildcard is used
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function guess()
    {
        if (strpos($this->getBaseName(), static::WILDCARD) !== false) {
            $names = $this->getDir()->glob(static::WILDCARD);
            if (!empty($names)) {
                $this->path = reset($names);
            }
        }

        return $this;
    }

    /**
     * Get last lines
     * Optimized for huge files
     *
     * @param int    $lines     Line count numbered from end
     * @param string $separator New Line separator
     *
     * @return array
     * @throws \LogicException
     */
    function tail($lines, $separator = PHP_EOL)
    {
        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist so cannot get last lines of it.", $this->path));
        }

        $handle = fopen($this->path, 'r');

        $count = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();

        while ($count > 0) {
            $t = ' ';
            while ($t != $separator) {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }

            $count--;

            if ($beginning) {
                rewind($handle);
            }

            $text[$lines - $count - 1] = trim(fgets($handle), $separator);

            if ($beginning) {
                break;
            }
        }

        fclose($handle);

        $text = array_reverse($text);

        return $text;
    }

    /**
     * Run binary
     * Returns status code and output text as result
     *
     * @param string $args Arguments
     *
     * @return array
     * @throws \LogicException
     */
    public function execute($args)
    {
        if (!$this->exists()) {
            throw new \LogicException(sprintf("File '%s' does not exist so it cannot be executed.", $this->path));
        }

        $command = $this->path;
        if (!empty($args)) {
            $command .= ' ' . $args;
        }

        $code = null;
        $result = null;
        exec($command, $result, $code);

        $data = array(
            'code' => (int) $code,
            'result' => $result
        );

        return $data;
    }

    /**
     * Check whether equals to another file
     *
     * @param File|string $file File object or path
     *
     * @return boolean
     */
    public function equals($file)
    {
        if (!$file instanceof File) {
            $file = new self($file);
        }

        $same = ($this->getAbsolutePath() === $file->getAbsolutePath());

        return $same;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->path;
    }
}