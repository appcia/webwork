<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;
use Appcia\Webwork\Core\Component;

/**
 * Base for view helper (PHP renderer tool)
 *
 * @package Appcia\Webwork\View
 */
abstract class Helper extends Component
{
    /**
     * Attached view
     *
     * @var View
     */
    protected $view;

    /**
     * Creator
     *
     * @param mixed $data Config data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        $helper = null;
        $type = null;
        $config = null;
        $namespaces = array(__CLASS__);

        if ($data instanceof Config) {
            $data = $data->getData();
        }

        if (is_string($data)) {
            $type = $data;
        } elseif (is_array($data)) {
            if (!isset($data['type'])) {
                throw new \InvalidArgumentException("View helper data does not contain 'type' key.");
            }

            $type = (string) $data['type'];

            if (isset($data['namespace'])) {
                if (!is_array($data['namespace'])) {
                    throw new \InvalidArgumentException("View helper namespaces should be an array.");
                }

                $namespaces = array_merge($namespaces, $data['namespace']);
            }

            $config = new Config($data);
        } else {
            throw new \InvalidArgumentException("View helper data has invalid format.");
        }

        $class = $type;
        if (!class_exists($class)) {
            foreach ($namespaces as $namespace) {
                $class = trim($namespace, '\\') . '\\' . ucfirst($type);

                if (class_exists($class)) {
                    break;
                }
            }
        }

        if (!class_exists($class) || !is_subclass_of($class, __CLASS__)) {
            throw new \InvalidArgumentException(sprintf("View helper '%s' is invalid or unsupported.", $type));
        }

        $helper = new $class();

        if ($config !== null) {
            $config->inject($helper);
        }

        return $helper;
    }

    /**
     * Get another helper
     *
     * @param string $name
     *
     * @return Helper
     */
    public function getHelper($name)
    {
        return $this->getView()
            ->getRenderer()
            ->getHelper($name);
    }

    /**
     * Get attached view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set attached view
     *
     * @param View $view
     *
     * @return $this
     */
    public function setView(View $view)
    {
        $this->view = $view;

        return $this;
    }
}