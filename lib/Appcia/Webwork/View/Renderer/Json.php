<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

class Json extends Renderer
{
    /**
     * Encoding options
     *
     * @var int
     */
    private $options;

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
     * @return Json
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