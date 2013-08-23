<?

namespace Appcia\Webwork\Intl\Translator;

use Appcia\Webwork\Intl\Translator;
use Appcia\Webwork\Web\Context;

/**
 * Native translator
 *
 * @package Appcia\Webwork\Intl\Translator
 */
class Php extends Translator
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function translate($id)
    {
        if (!array_key_exists($id, $this->data)) {
            return NULL;
        }

        return $this->data[$id];
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}