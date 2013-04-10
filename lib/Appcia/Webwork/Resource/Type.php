<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource\Manager;
use Appcia\Webwork\System\File;

class Type
{

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $params;

    /**
     * @var File
     */
    private $file;

    public function __construct(Manager $manager, $name, array $params = array())
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->params = $params;
        $this->file = null;
    }

    /**
     * Get origin factory
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param bool $force Throw exception when cannot be done
     *
     * @return File|null
     * @throws Exception
     */
    public function getFile($force = false)
    {
        if ($this->file === null) {
            $file = $this->manager->determineFile($this->name, $this->params);

            if ($file !== null) {
                $this->params['ext'] = $file->getExtension();
                $this->file = $file;
            } else if ($force) {
                throw new Exception(sprintf("Cannot determine target file for resource '%s'", $this->name));
            }
        }

        return $this->file;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $file = $this->getFile();

        if ($file === null) {
            return '';
        } else {
            return $file->__toString();
        }
    }

}