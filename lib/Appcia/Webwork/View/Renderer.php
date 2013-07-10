<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\Renderer\Ini;
use Appcia\Webwork\View\Renderer\Json;
use Appcia\Webwork\View\Renderer\Php;
use Appcia\Webwork\View\Renderer\Xml;
use Appcia\Webwork\View\View;

/**
 * Base for view renderer
 *
 * @package Appcia\Webwork\View
 */
abstract class Renderer
{
    /**
     * Source view
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
        $renderer = null;
        $type = null;
        $config = null;

        if ($data instanceof Config) {
            $data = $data->getData();
        }

        if (is_string($data)) {
            $type = $data;
        } elseif (is_array($data)) {
            if (!isset($data['type'])) {
                throw new \InvalidArgumentException("View renderer data should has a key 'type'.");
            }
            $type = (string) $data['type'];

            if (!empty($data['config'])) {
                $config = new Config($data['config']);
            }
        } else {
            throw new \InvalidArgumentException("View renderer data has invalid format.");
        }

        $class = $type;
        if (!class_exists($class)) {
            $class =  __CLASS__ . '\\' . ucfirst($type);
        }

        if (!class_exists($class) || !is_subclass_of($class, __CLASS__)) {
            throw new \InvalidArgumentException(sprintf("View renderer '%s' is invalid or unsupported.", $type));
        }

        $renderer = new $class();

        if ($config !== null) {
            $config->inject($renderer);
        }

        return $renderer;
    }

    /**
     * Get view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set view
     *
     * @param View $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get rendered content
     *
     * @param string|null Template
     *
     * @return string
     */
    abstract public function render($template = null);
}