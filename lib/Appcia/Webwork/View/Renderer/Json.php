<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\Exception\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

/**
 * JSON view renderer
 *
 * @package Appcia\Webwork\View\Renderer
 */
class Json extends Renderer
{
    /**
     * Encoding options
     *
     * @var int
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct($options = 0)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template = null)
    {
        $data = $this->getView()
            ->getData();

        $content = json_encode($data, $this->options);

        return $content;
    }

    /**
     * @param int $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return int
     */
    public function getOptions()
    {
        return $this->options;
    }
}