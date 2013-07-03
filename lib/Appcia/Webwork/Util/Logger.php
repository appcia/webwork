<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\Model\Template;
use Appcia\Webwork\System\File;

class Logger
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const NOTICE = 'notice';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';

    /**
     * Available levels
     *
     * @var array
     */
    protected static $levels = array(
        self::DEBUG,
        self::INFO,
        self::NOTICE,
        self::WARNING,
        self::ERROR,
        self::CRITICAL,
        self::ALERT,
        self::EMERGENCY
    );

    /**
     * Storage file
     *
     * @var File
     */
    protected $file;

    /**
     * Write callback
     * Useful for delivering variable parameters (like date) to template
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Message template
     * Auto filled params: date, message, level
     *
     * @var Template
     */
    protected $template;

    /**
     * Constructor
     */
    public function __construct($file)
    {
        $this->template = new Template('[{level}] {date} {message}');

        if (!$file instanceof File) {
            $file = new File($file);
        }

        $this->file = $file;
    }

    /**
     * @return array
     */
    public static function getLevels()
    {
        return self::$levels;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get message template
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template
     * Auto filled params: date, message, level
     *
     * @param Template|string $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!$template instanceof Template) {
            $template = new Template($template);
        }

        $this->template = $template;

        return $this;
    }

    /**
     * Get last logs
     *
     * @param int $count Line count numbered from end
     *
     * @return null|string
     */
    public function tail($count)
    {
        if (!$this->file->exists()) {
            return null;
        }

        $lines = $this->file->tail($count);
        $data = implode($lines, PHP_EOL);

        return $data;
    }

    /**
     * Write debug message
     *
     * @param string $message
     *
     * @return $this
     */
    public function debug($message)
    {
        $this->write($message, self::DEBUG);

        return $this;
    }

    /**
     * Write log message
     *
     * @param string $message Message
     * @param string $level   Level
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function write($message, $level)
    {
        if (empty($message)) {
            throw new \InvalidArgumentException(sprintf("Logger write error. Message cannot be empty."));
        }

        if (!in_array($level, self::$levels)) {
            throw new \InvalidArgumentException(sprintf("Logger write error. Invalid level '%s'", $level));
        }

        $date = new \DateTime('now');

        $this->template->set('level', mb_strtoupper($level))
            ->set('message', trim($message))
            ->set('date', $date->format('Y-m-d H:i:s.u'));

        if ($this->callback !== null && is_callable($this->callback)) {
            call_user_func($this->callback, $this->template);
        }

        $text = $this->template->render();
        $this->file->append($text);

        return $this;
    }

    /**
     * Write info message
     *
     * @param $message
     *
     * @return $this
     */
    public function info($message)
    {
        $this->write($message, self::INFO);

        return $this;
    }

    /**
     * Write notice message
     *
     * @param $message
     *
     * @return $this
     */
    public function notice($message)
    {
        $this->write($message, self::NOTICE);

        return $this;
    }

    /**
     * Write warning message
     *
     * @param $message
     *
     * @return $this
     */
    public function warning($message)
    {
        $this->write($message, self::WARNING);

        return $this;
    }

    /**
     * Write error message
     *
     * @param $message
     *
     * @return $this
     */
    public function error($message)
    {
        $this->write($message, self::ERROR);

        return $this;
    }

    /**
     * Write warning message
     *
     * @param $message
     *
     * @return $this
     */
    public function critical($message)
    {
        $this->write($message, self::CRITICAL);
    }

    /**
     * Write alert message
     *
     * @param $message
     *
     * @return $this
     */
    public function alert($message)
    {
        $this->write($message, self::ALERT);

        return $this;
    }

    /**
     * Write emergency message
     *
     * @param $message
     *
     * @return $this
     */
    public function emergency($message)
    {
        $this->write($message, self::EMERGENCY);
    }
}