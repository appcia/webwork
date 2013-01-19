<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Session;

class Flash
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $messages;

    /**
     * Constructor
     *
     * @param Session $session
     * @param string $namespace
     */
    public function __construct(Session $session, $namespace = 'flash')
    {
        $this->session = $session;
        $this->namespace = $namespace;
        $this->messages = array();

        $this->load();
    }

    /**
     * Load messages from storage
     *
     * @return Flash
     */
    private function load()
    {
        if ($this->session->has($this->namespace)) {
            $this->messages = $this->session->get($this->namespace);
        }

        return $this;
    }

    /**
     * Save messages in storage
     *
     * @return Flash
     */
    private function save()
    {
        $this->session->set($this->namespace, $this->messages);

        return $this;
    }

    /**
     * Add flash message to session storage
     *
     * @param $message
     * @param $type
     *
     * @return Flash
     */
    public function addMessage($message, $type)
    {
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = array();
        }

        $this->messages[$type][] = (string) $message;
        
        $this->save();

        return $this;
    }

    /**
     * Clear specific flash messages (or all)
     *
     * @param string $type
     *
     * @return Flash
     */
    public function clearMessages($type = null)
    {
        if ($type) {
            unset($this->messages[$type]);
        } else {
            $this->messages = array();
        }

        $this->save();

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
     * @param array $messages
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
     * @param string $type
     *
     * @return array
     */
    public function getMessages($type = null)
    {
        if ($type) {
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
     * @param string $type
     *
     * @return array
     */
    public function popMessages($type = null)
    {
        $messages = array();

        if ($type) {
            if (isset($this->messages[$type])) {
                $messages = $this->messages[$type];
                $this->messages[$type] = array();
            }
        } else {
            $messages = $this->flattenMessages();
            $this->messages = array();
        }

        $this->save();

        return $messages;
    }

    /**
     * Add success message
     *
     * @param $message
     *
     * @return Flash
     */
    public function success($message)
    {
        $this->addMessage($message, 'success');

        return $this;
    }

    /**
     * Add information message
     *
     * @param $message
     *
     * @return Flash
     */
    public function info($message)
    {
        $this->addMessage($message, 'info');

        return $this;
    }

    /**
     * Add warning message
     *
     * @param $message
     *
     * @return Flash
     */
    public function warning($message)
    {
        $this->addMessage($message, 'warning');

        return $this;
    }

    /**
     * Add success message
     *
     * @param $message
     *
     * @return Flash
     */
    public function error($message)
    {
        $this->addMessage($message, 'error');

        return $this;
    }
}