<?

namespace Appcia\Webwork\System;

use Appcia\Webwork\Exception;

class Dir
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
        if ($path === null) {
            throw new Exception(sprintf("Invalid directory path"));
        }

        if ($path === '') {
            $path = '.';
        }

        $this->path = $path;
    }

    /**
     * Get path (optionally with filename)
     *
     * @param string $filename File name
     *
     * @return string
     */
    public function getPath($filename = null)
    {
        if ($filename === null) {
            return $this->path;
        }

        return $this->path . '/' . $filename;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return basename($this->path);
    }

    /**
     * Get absolute path
     * Returns null if cannot be determined
     *
     * @return string|null
     */
    public function getAbsolutePath()
    {
        $path = realpath($this->path);

        if ($path === false) {
            return null;
        }

        return $path;
    }

    /**
     * Get relative directory
     *
     * @param string $path Path
     *
     * @return Dir
     */
    public function getRelative($path)
    {
        if (!empty($path)) {
            $path = $this->path . '/' . $path;
        }

        return new self($path);
    }

    /**
     * Get root directory
     *
     * @return Dir
     */
    public function getRoot()
    {
        $paths = explode('/', $this->path);

        if (empty($paths)) {
            return null;
        }

        return new self($paths[0]);
    }

    /**
     * Get parent directory
     *
     * @return Dir
     */
    public function getParent()
    {
        $path = dirname($this->path);

        return new self($path);
    }

    /**
     * Get current working directory
     *
     * @return Dir
     */
    public static function getCurrent()
    {
        $path = getcwd();

        return new self($path);
    }

    /**
     * Get home directory
     *
     * @return Dir
     */
    public static function getHome()
    {
        $path = getenv('HOME');

        return new self($path);
    }

    /**
     * Creates a directory
     *
     * @param int  $permission Value for CHMOD
     * @param bool $recursive  Create also parent directories
     *
     * @return $this
     * @throws Exception
     */
    public function create($permission = 0777, $recursive = true)
    {
        if (!@mkdir($this->path, $permission, $recursive)) {
            throw new Exception(sprintf(
                'Cannot create a directory: %s ' . PHP_EOL
                    . 'Verify access permissions', $this->path
            ));
        }

        return $this;
    }

    /**
     * Delete all specified paths and create them again with specified permission
     *
     * @param array $paths      Paths
     * @param int   $permission Value for CHMOD
     *
     * @return Dir
     */
    public function flush(array $paths, $permission = 0777)
    {
        foreach ($paths as $path) {
            $dir = new self($path);
            $dir->getRoot()->remove();
        }

        foreach ($paths as $path) {
            $dir = new self($path);
            $dir->create($permission);
        }

        return $this;
    }

    /**
     * Removes a directory
     *
     * @param bool $recursive Deletes all subdirectories and files
     *
     * @return Dir
     * @throws Exception
     */
    public function remove($recursive = true)
    {
        if ($recursive) {
            $this->removeRecursive($this->path);

            if (is_dir($this->path)) {
                throw new Exception(sprintf(
                    'Cannot remove a directory: %s ' . PHP_EOL
                        . 'Verify access permissions', $this->path
                ));
            }
        } else {
            if (!@rmdir($this->path)) {
                throw new Exception(sprintf(
                    'Cannot remove a directory: %s ' . PHP_EOL
                        . 'Make sure that it is empty or specify recursive parameter', $this->path
                ));
            }
        }

        return $this;
    }

    /**
     * Recursive helper for remove
     *
     * @param string $path Current tree node
     *
     * @return void
     */
    private function removeRecursive($path)
    {
        if (!is_dir($path)) {
            return;
        }

        $nodes = @scandir($path);
        if ($nodes === false) {
            return;
        }

        $nodes = array_diff($nodes, array('.', '..'));
        foreach ($nodes as $node) {
            $type = @filetype($path . '/' . $node);
            if ($type === false) {
                continue;
            }

            if ($type == 'dir') {
                $this->removeRecursive($path . '/' . $node);
            } else {
                @unlink($path . '/' . $node);
            }
        }

        reset($nodes);
        rmdir($path);
    }

    /**
     * Create a symlink pointing to this directory
     *
     * @param Dir|string $dir         Dir object or path
     * @param bool       $createPaths Create paths (if don't exist)
     *
     * @return Dir
     * @throws Exception
     */
    public function symlink($dir, $createPaths = true)
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Cannot create a link. Directory must exist: '%s'", $this->path));
        }

        if (!$dir instanceof self) {
            $dir = new self($dir);
        }

        if ($dir->isLink() && !@unlink($dir->getPath())) {
            throw new Exception(sprintf("Cannot remove an existing link: '%s'", $dir));
        }

        $parent = $dir->getParent();

        if ($createPaths) {
            if (!$this->exists()) {
                $this->create();
            }

            if (!$parent->exists()) {
                $parent->create();
            }
        }

        if (!@symlink($this->getAbsolutePath(), $parent->getAbsolutePath() . '/' . $dir->getName())) {
            throw new Exception(sprintf("Cannot create a link to directory: %s -> %s", $this->getPath(), $dir->getPath()));
        }

        return $this;
    }

    /**
     * Generate non-existing file (optionally with specified extension)
     *
     * @param string|null $extension Extension
     * @param string|null $prefix    Name prefix
     * @param string|null $suffix    Name suffix
     *
     * @return File
     */
    public function generateRandomFile($extension = null, $prefix = null, $suffix = null)
    {
        do {
            $path = $this->path . '/';

            if ($prefix !== null) {
                $path .= (string) $prefix;
            }

            $path .= uniqid('', true);

            if ($suffix !== null) {
                $path .= (string) $suffix;
            }

            if ($extension !== null) {
                $path .= '.' . (string) $extension;
            }
        } while (file_exists($path));

        return new File($path);
    }

    /**
     * Find files that matched specified pattern
     *
     * @param string $pattern Pattern (with wildcards etc)
     *
     * @return array
     * @throws Exception
     */
    public function glob($pattern)
    {
        $pattern = $this->path . '/' . $pattern;

        $files = @glob($pattern);
        if ($files === false) {
            throw new Exception(sprintf("Cannot glob path '%s' with pattern: '%s'", $this->path, $pattern));
        }

        // For sure, when result is null (on some systems)
        if (empty($files)) {
            return array();
        }

        return $files;
    }

    /**
     * Check whether it really exist
     *
     * @return bool
     */
    public function exists()
    {
        return is_dir($this->path);
    }

    /**
     * Check whether is a symbolic link
     *
     * @return bool
     */
    public function isLink()
    {
        return is_link($this->path);
    }

    /**
     * Check whether is writable
     *
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * Check whether is empty
     *
     * @return bool
     * @throws Exception
     */
    public function isEmpty()
    {
        $contents = @scandir($this->path);
        if ($contents === false) {
            throw new Exception(sprintf("Cannot check that directory is empty: '%s'", $this->path));
        }

        $contents = array_diff($contents, array('.', '..'));
        $empty = empty($contents);

        return $empty;
    }
}