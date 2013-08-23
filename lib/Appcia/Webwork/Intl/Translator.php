<?

namespace Appcia\Webwork\Intl;

use Appcia\Webwork\Core\Component;
use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Context;

/**
 * Translating texts between languages
 *
 * @package Appcia\Webwork\Intl
 */
abstract class Translator extends Object
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