<?

namespace Appcia\Webwork\System;

use Appcia\Webwork\Data\Encrypter;

/**
 * Filesystem directory representation.
 *
 * Does not necessarily refer to an existing directory.
 */
class Dir
{
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
        if ($path === null) {
            throw new \InvalidArgumentException("Directory is not specified");
        }

        if ($path === '') {
            $path = '.';
        }

        $this->path = $path;
    }

    /**
     * Get current working directory
     *
     * @return $this
     */
    public static function getCurrent()
    {
        $path = getcwd();

        return new self($path);
    }

    /**
     * Get home directory
     *
     * @return $this
     */
    public static function getHome()
    {
        $path = getenv('HOME');

        return new self($path);
    }

    /**
     * Get relative directory
     *
     * @param string $path Path
     *
     * @return $this
     */
    public function getRelativePath($path)
    {
        if (!empty($path)) {
            $path = $this->path . '/' . $path;
        }

        return new self($path);
    }

    /**
     * Delete all specified paths and create them again with specified permission
     *
     * @param array $paths      Paths
     * @param int   $permission Value for CHMOD
     *
     * @return $this
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
     * @param boolean $recursive Deletes all subdirectories and files
     *
     * @return $this
     */
    public function remove($recursive = true)
    {
        if ($recursive) {
            $this->removeRecursive($this->path);
        } else {
            rmdir($this->path);
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
    protected function removeRecursive($path)
    {
        if (!is_dir($path)) {
            return;
        }

        $nodes = scandir($path);
        if ($nodes === false) {
            return;
        }

        $nodes = array_diff($nodes, array('.', '..'));
        foreach ($nodes as $node) {
            $type = filetype($path . '/' . $node);
            if ($type === false) {
                continue;
            }

            if ($type == 'dir') {
                $this->removeRecursive($path . '/' . $node);
            } else {
                unlink($path . '/' . $node);
            }
        }

        reset($nodes);
        rmdir($path);
    }

    /**
     * Get root directory
     *
     * @return $this
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
     * Creates a directory
     *
     * @param int     $permission Value for CHMOD
     * @param boolean $recursive  Create also parent directories
     *
     * @return $this
     * @throws \ErrorException
     */
    public function create($permission = 0777, $recursive = true)
    {
        if ($this->exists()) {
            return $this;
        }

        if (!@mkdir($this->path, $permission, $recursive)) {
            throw new \ErrorException(sprintf(
                "Directory '%s' cannot be created in '%s' because probably it is not writable.",
                $this->getName(),
                $this->getParent()->getPath()
            ));
        }

        return $this;
    }

    /**
     * Check whether it really exist
     *
     * @return boolean
     */
    public function exists()
    {
        return is_dir($this->path);
    }

    /**
     * Get a name
     *
     * @return string
     */
    public function getName()
    {
        $name = basename($this->path);

        return $name;
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

        $path = $this->path . '/' . $filename;

        return $path;
    }

    /**
     * Get parent directory
     *
     * @return $this
     */
    public function getParent()
    {
        $path = dirname($this->path);

        return new self($path);
    }

    /**
     * Create a symlink pointing to this directory
     *
     * @param Dir|string $dir         Dir object or path
     * @param boolean    $createPaths Create paths (if does not exist)
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function symlink($dir, $createPaths = true)
    {
        if (!$this->exists()) {
            throw new \InvalidArgumentException(sprintf(
                "Cannot create a link. Directory must exist: '%s'", $this->path
            ));
        }

        if (!$dir instanceof self) {
            $dir = new self($dir);
        }

        if ($dir->isLink()) {
            unlink($dir->getPath());
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

        symlink($this->getAbsolutePath(), $parent->getAbsolutePath() . '/' . $dir->getName());

        return $this;
    }

    /**
     * Check whether is a symbolic link
     *
     * @return boolean
     */
    public function isLink()
    {
        return is_link($this->path);
    }

    /**
     * Get an absolute path
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
     * Generate non-existing file (optionally with specified extension)
     *
     * @param string|null $extension Extension
     * @param string|null $prefix    Name prefix
     * @param string|null $suffix    Name suffix
     *
     * @return File
     */
    public function randFile($extension = null, $prefix = null, $suffix = null)
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
     * Generate hashed filename with same extension
     *
     * @param string    $source    Path
     * @param Encrypter $encrypter Encrypter
     *
     * @return File
     */
    public function hashFile($source, Encrypter $encrypter = null)
    {
        if (!$source instanceof File) {
            $source = new File($source);
        }

        if ($encrypter === null) {
            $encrypter = new Encrypter();
        }

        $hash = $encrypter->crypt($source->getAbsolutePath());
        $path = $this->getPath();
        $ext = $source->getExtension();

        $target = new File($path . '/' . $hash . '.' . $ext);

        return $target;
    }

    /**
     * Find files that matched specified pattern
     *
     * @param string $pattern Pattern (with wildcards etc)
     *
     * @return array
     */
    public function glob($pattern)
    {
        $pattern = $this->path . '/' . $pattern;
        $files = glob($pattern);

        // For sure, when result is null (on some systems)
        if (empty($files)) {
            return array();
        }

        return $files;
    }

    /**
     * Check whether is writable
     *
     * @return boolean
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * Get all files in directory
     *
     * @return File[]
     */
    public function getFiles()
    {
        $contents = $this->getContents();
        $files = array_filter($contents, function ($content) {
            return $content instanceof File;
        });

        return $files;
    }

    /**
     * Get all contents (files and directories)
     *
     * @return array
     */
    public function getContents()
    {
        $contents = scandir($this->path);
        $contents = array_diff($contents, array('.', '..'));

        foreach ($contents as $key => $content) {
            $path = $this->path . '/' . $content;

            if (is_file($path)) {
                $contents[$key] = new File($content);
            } elseif (is_dir($path)) {
                $contents[$key] = new Dir($content);
            } else {
                unset($contents[$key]);
            }
        }

        return $contents;
    }

    /**
     * Get all sub directories
     *
     * @return $this[]
     */
    public function getDirs()
    {
        $contents = $this->getContents();
        $dirs = array_filter($contents, function ($content) {
            return $content instanceof self;
        });

        return $dirs;
    }

    /**
     * Check whether is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $contents = $this->getContents();
        $empty = empty($contents);

        return $empty;
    }
}