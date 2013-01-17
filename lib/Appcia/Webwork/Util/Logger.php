<?

namespace Appcia\Webwork\Util;

class Logger
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var string
     */
    private $messageFormat;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateFormat = 'Y-m-d H:i:s';
        $this->messageFormat = '{level}: {date} {message}';
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return Logger
     * @throws \InvalidArgumentException
     */
    public function setFile($file)
    {
        $file = (string) $file;

        if (!file_exists($file)) {
            @touch($file);

            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf("Cannot create log file: '%s'", $file));
            }
        }

        if (!is_writable($file)) {
            throw new \InvalidArgumentException(sprintf("Log file is not writeable: '%s'", $file));
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Set date format
     *
     * @param $format
     * @return Logger
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = (string) $format;

        return $this;
    }

    /**
     * Get date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Get date format
     *
     * @param string $messageFormat
     *
     * @return Logger
     */
    public function setMessageFormat($messageFormat)
    {
        $this->messageFormat = (string) $messageFormat;

        return $this;
    }

    /**
     * Get message format
     *
     * @return string
     */
    public function getMessageFormat()
    {
        return $this->messageFormat;
    }

    /**
     * Write log message
     *
     * @param string $message Message
     * @param string $level   Level
     *
     * @return void
     * @throws \LogicException
     * @throws \ErrorException
     */
    public function write($message, $level)
    {
        if ($this->file === null) {
            throw new \LogicException("Log file is not specified");
        }

        $message = str_replace(
            array('{level}', '{date}', '{message}'),
            array(mb_strtoupper($level), date($this->dateFormat), (string) $message),
            $this->messageFormat
        ) . PHP_EOL;

        if (!@file_put_contents($this->file, $message, FILE_APPEND)) {
            throw new \ErrorException(sprintf("Cannot write message to log file: '%s'", $this->file));
        }
    }

    /**
     * Write debug message
     *
     * @param $message
     *
     * @return void
     */
    public function debug($message)
    {
        $this->write($message, 'debug');
    }

    /**
     * Write info message
     *
     * @param $message
     *
     * @return void
     */
    public function info($message)
    {
        $this->write($message, 'info');
    }

    /**
     * Write warning message
     *
     * @param $message
     *
     * @return void
     */
    public function warn($message)
    {
        $this->write($message, 'warn');
    }

    /**
     * Write error message
     *
     * @param $message
     *
     * @return void
     */
    public function error($message)
    {
        $this->write($message, 'error');
    }
}