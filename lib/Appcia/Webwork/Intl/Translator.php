<?

namespace Appcia\Webwork\Intl;

use Appcia\Webwork\Core\Component;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Context;

/**
 * Translating texts between languages
 *
 * @package Appcia\Webwork\Intl
 */
abstract class Translator extends Component
{
    /**
     * Creator
     *
     * @param mixed $data Config data
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public static function create($data)
    {
        return Config::create($data, __CLASS__);
    }

    /**
     * Translate a message
     *
     * @param string $id Message ID
     *
     * @return mixed
     */
    abstract public function translate($id);
}