<?

namespace Appcia\Webwork\View;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\Renderer\Ini;
use Appcia\Webwork\View\Renderer\Json;
use Appcia\Webwork\View\Renderer\Php;
use Appcia\Webwork\View\Renderer\Xml;
use Appcia\Webwork\View\View;

abstract class Renderer
{
    const PHP = 'php';

    const JSON = 'json';

    const XML = 'xml';

    const INI = 'ini';

    /**
     * Built-in types
     *
     * @var array
     */
    private static $types = array(
        self::PHP,
        self::JSON,
        self::XML,
        self::INI
    );

    /**
     * Source view
     *
     * @var View
     */
    private $view;

    /**
     * Get available types
     *
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * Create renderer from config
     *
     * @param string|array $data Data
     *
     * @return Renderer
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        $type = null;
        $config = null;

        // Parse data
        if (is_string($data)) {
            $type = $data;
        } elseif (is_array($data)) {
            if (!isset($data['type'])) {
                throw new \InvalidArgumentException("View renderer config should have a key 'type");
            }
            $type = $data['type'];

            if (!empty($data['config'])) {
                $config = new Config($data['config']);
            }
        } else {
            throw new \InvalidArgumentException('View renderer cannot be created. Invalid data specified');
        }

        // Create valid object
        $renderer = null;

        switch ($type) {
            case self::PHP:
                $renderer = new Php();
                break;
            case self::JSON:
                $renderer = new Json();
                break;
            case self::XML:
                $renderer = new Xml();
                break;
            case self::INI:
                $renderer = new Ini();
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    "View renderer '%s' is invalid or unsupported / not built-in", $renderer
                ));
                break;
        }

        // Inject configuration
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
     * @return Renderer
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