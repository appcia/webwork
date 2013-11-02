<?

namespace Appcia\Webwork\Intl;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Context;

/**
 * Translating texts between languages
 *
 * @package Appcia\Webwork\Intl
 */
abstract class Translator implements Object
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * Translate a message
     *
     * @param string $id Message ID
     *
     * @return mixed
     */
    abstract public function translate($id);

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}