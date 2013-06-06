<?

namespace Appcia\Webwork\Intl;

use Appcia\Webwork\Intl\Translator\Gettext;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Storage\Config;

/**
 * Translating texts between languages
 *
 * @package Appcia\Webwork\Intl
 */
abstract class Translator
{
    /**
     * Types
     */
    const GETTEXT = 'gettext';

    /**
     * @var Context
     */
    private $context;

    /**
     * Creator
     *
     * @param array|string $data Config data
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public static function create($data)
    {
        $type = null;
        $config = null;

        if (is_string($data)) {
            $type = $data;
        } elseif (is_array($data)) {
            if (!isset($data['type'])) {
                throw new \InvalidArgumentException("Translator config should has key 'type'.");
            }

            $type = $data['type'];

            if (isset($data['config'])) {
                $config = new Config($data['config']);
            }
        } else {
            throw new \InvalidArgumentException("Translator config should be an array.");
        }

        $translator = null;

        switch ($type) {
            case self::GETTEXT:
                $translator = new Gettext();
                break;
            default:
                throw new \OutOfBoundsException(sprintf("Translator type '%s' is invalid or unsupported.", $type));
                break;
        }

        if ($config !== null) {
            $config->inject($translator);
        }

        return $translator;
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
     * Get context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set context
     *
     * @param Context $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
}