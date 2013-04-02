<?

namespace Appcia\Webwork;

use Appcia\Webwork\System\File;

class Resource
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var bool
     */
    private $temporary;

    /**
     * Constructor
     */
    public function __construct($file)
    {
        if (!$file instanceof File) {
            $file = new File($file);
        }

        $this->file = $file;
        $this->temporary = false;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param bool $temporary
     */
    public function setTemporary($temporary)
    {
        $this->temporary = $temporary;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * Compare resources on same filesystem
     *
     * @param Resource $resource Resource
     *
     * @return bool
     * @throws Exception
     */
    public function isEqualTo($resource)
    {
        if (!$resource instanceof self) {
            throw new Exception('Invalid resource to be compared');
        }

        return $this->getFile()->getAbsolutePath() === $resource->getFile()->getAbsolutePath();
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getFile()
            ->getPath();
    }

}

