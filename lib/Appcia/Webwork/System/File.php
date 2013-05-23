<?

namespace Appcia\Webwork\System;

use Appcia\Webwork\Exception\Exception;

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
        unlink($this->path);

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
        touch($this->path);

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

        rename($this->path, $file->path);

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
     * @throws Exception
     */
    public function read()
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Cannot read from non-existing file: '%s'", $this->path));
        }

        $data = file_get_contents($this->path);

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

        file_put_contents($this->path, $data);

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

        file_put_contents($this->path, $data, FILE_APPEND);

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
     * @throws Exception
     */
    function tail($lines, $separator = PHP_EOL)
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("File does not exist: '%s'", $this->path));
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
     * @throws Exception
     */
    public function execute($args)
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Cannot execute program. File does not exist: '%s'", $this->path));
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
     * @return bool
     */
    public function equals($file)
    {
        if (!$file instanceof File) {
            $file = new self($file);
        }

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