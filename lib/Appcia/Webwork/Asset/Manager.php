<?

namespace Appcia\Webwork\Asset;

use Appcia\Webwork\Core\App;
use Appcia\Webwork\System\Dir;

class Manager
{
    /**
     * Directory for filtered asset files
     *
     * @var Dir
     */
    protected $dir;

    /**
     * Used content filters
     *
     * @var Filter[]
     */
    protected $filters;

    /**
     * Force regenerating
     *
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->assets = array();
        $this->filters = array();
        $this->debug = false;
        $this->setDir('web/asset');
    }

    /**
     * Set filtered asset files directory
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setDir($dir)
    {
        if (!$dir instanceof $dir) {
            $dir = new Dir($dir);
        }
        $this->dir = $dir;

        return $this;
    }

    /**
     * Get filtered asset files directory
     *
     * @return Dir
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Is force regenerating enabled
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Set force regenerating
     *
     * @param $flag
     *
     * @return $this
     */
    public function setDebug($flag)
    {
        $this->debug = (bool) $flag;

        return $this;
    }

    /**
     * Get asset filter by name
     *
     * @param string $name Name
     *
     * @return Filter
     */
    public function getFilter($name)
    {
        if (!isset($this->filters[$name])) {
            $config = $this->app->getConfig()
                ->grab('asset.filter')
                ->set('class', $name);

            $filter = Filter::objectify($config);

            $this->filters[$name] = $filter;
        }

        return $this->filters[$name];
    }
}