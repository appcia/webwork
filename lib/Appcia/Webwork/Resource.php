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
     * @var string
     */
    private $token;

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
     * Set file
     *
     * @param File $file
     *
     * @return Resource
     */
    public function setFile($file)
    {
        if (!$file instanceof File) {
            $file = new File($file);
        }

        $this->file = $file;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Mark / unmark as temporary
     *
     * @param bool $temporary
     *
     * @return Resource
     */
    public function setTemporary($temporary)
    {
        $this->temporary = (bool) $temporary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return Resource
     */
    public function setToken($token)
    {
        $this->token = (string) $token;

        return $this;
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
        $path = (string) $this->getFile()
            ->getPath();

        return $path;
    }

}

