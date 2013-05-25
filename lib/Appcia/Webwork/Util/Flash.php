<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Storage\Session\Space;

/**
 * Basic flash messenger
 *
 * Is using session space as storage
 *
 * @package Appcia\Webwork\Util
 */
class Flash
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';

    /**
     * Message storage
     *
     * @var Space
     */
    private $messages;

    /**
     * Constructor
     *
     * @param Space $space Session space
     */
    public function __construct(Space $space)
    {
        $space->setAutoflush(true);
        $this->messages = $space;
    }

    /**
     * Add flash message to session storage
     *
     * @param string $message Text
     * @param string $type    Type
     *
     * @return Flash
     */
    public function addMessage($message, $type)
    {
        $messages = (array) $this->messages[$type];
        $messages[] = (string) $message;

        $this->messages[$type] = $messages;

        return $this;
    }

    /**
     * Clear specific flash messages (or all)
     *
     * @param string $type Type
     *
     * @return Flash
     */
    public function clearMessages($type = null)
    {
        if ($type !== null) {
            unset($this->messages[$type]);
        } else {
            $this->messages = array();
        }

        return $this;
    }

    /**
     * Flatten messages (skip type information)
     *
     * @return array
     */
    private function flattenMessages()
    {
        $messages = array();
        foreach ($this->messages as $type) {
            foreach ($type as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Set messages
     *
     * @param array $messages Data
     *
     * @return array
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get specific flash messages (or all)
     *
     * @param string $type Type
     *
     * @return array
     */
    public function getMessages($type = null)
    {
        if ($type !== null) {
            if (!isset($this->messages[$type])) {
                return array();
            } else {
                return $this->messages[$type];
            }
        } else {
            return $this->flattenMessages();
        }
    }

    /**
     * Get specific flash messages (or all)
     *
     * @param string $type Type
     *
     * @return array
     */
    public function popMessages($type = null)
    {
        $messages = array();

        if ($type !== null) {
            if (isset($this->messages[$type])) {
                $messages = $this->messages[$type];
                $this->messages[$type] = array();
            }
        } else {
            $messages = $this->flattenMessages();
            $this->messages = array();
        }

        return $messages;
    }

    /**
     * Add success message
     *
     * @param string $message Text
     *
     * @return Flash
     */
    public function success($message)
    {
        $this->addMessage($message, self::SUCCESS);

        return $this;
    }

    /**
     * Add information message
     *
     * @param string $message Text
     *
     * @return Flash
     */
    public function info($message)
    {
        $this->addMessage($message, self::INFO);

        return $this;
    }

    /**
     * Add warning message
     *
     * @param string $message Text
     *
     * @return Flash
     */
    public function warning($message)
    {
        $this->addMessage($message, self::WARNING);

        return $this;
    }

    /**
     * Add success message
     *
     * @param string $message Text
     *
     * @return Flash
     */
    public function error($message)
    {
        $this->addMessage($message, self::ERROR);

        return $this;
    }
}