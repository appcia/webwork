<?

namespace Appcia\Webwork\Util;

use Appcia\Webwork\System\File;
use Appcia\Webwork\Exception;

class Logger
{
    /**
     * @var File
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
    public function __construct($file)
    {
        $this->dateFormat = 'Y-m-d H:i:s';
        $this->messageFormat = '{level}: {date} {message}';

        if (!$file instanceof File) {
            $file = new File($file);
        }

        $this->file = $file;
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
     * @throws Exception
     */
    public function write($message, $level)
    {
        $message = str_replace(
            array('{level}', '{date}', '{message}'),
            array(mb_strtoupper($level), date($this->dateFormat), (string) $message),
            $this->messageFormat
        ) . PHP_EOL;

        $this->file->append($message);
    }

    /**
     * Get last logs
     *
     * @param int $lines Line count numbered from end
     *
     * @return null|string
     */
    public function tail($lines)
    {
        if (!$this->file->exists()) {
            return null;
        }

        $data = implode($this->file->tail($lines), PHP_EOL);

        return $data;
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