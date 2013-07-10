<?

namespace Appcia\Webwork\Data;

/**
 * Data encoder
 *
 * @package Appcia\Webwork\Data
 */
class Encoder
{
    const PHP = 'php';
    const JSON = 'json';
    const BASE64 = 'base64';

    /**
     * @var array
     */
    protected static $encodings = array(
        self::PHP,
        self::JSON,
        self::BASE64
    );

    /**
     * @var int
     */
    protected $encoding;

    /**
     * Constructor
     *
     * @param string $encoding
     */
    public function __construct($encoding = self::BASE64)
    {
        $this->setEncoding($encoding);
    }

    /**
     * Creator
     *
     * @param string $encoding Encoding
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create($encoding)
    {
        if (!is_string($encoding)) {
            throw new \InvalidArgumentException('Encoder cannot be created. Invalid data specified.');
        }

        return new self($encoding);
    }

    /**
     * Get available encodings
     *
     * @return array
     */
    public static function getEncodings()
    {
        return self::$encodings;
    }

    /**
     * Get encoding
     *
     * @return int
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param int $encode
     *
     * @return $this
     * @throws \OutOfBoundsException
     */
    public function setEncoding($encode)
    {
        if (!in_array($encode, self::$encodings)) {
            throw new \OutOfBoundsException(sprintf("Encoding '%s' is invalid or unsupported.", $encode));
        }

        $this->encoding = $encode;

        return $this;
    }

    /**
     * Encode data
     *
     * @param array $value Value
     *
     * @return string
     */
    public function encode($value)
    {
        $data = null;
        switch ($this->encoding) {
            case self::PHP:
                $data = @serialize($value);
                break;
            case self::JSON:
                $data = json_encode($value);
                break;
            case self::BASE64:
                $data = @serialize($value);
                if ($data !== false) {
                    $data = base64_encode($data);
                }
                break;
        }

        return $data;
    }

    /**
     * Decode data
     *
     * @param string $value Value
     *
     * @return mixed
     */
    public function decode($value)
    {
        $data = null;

        switch ($this->encoding) {
            case self::PHP:
                $data = @unserialize($value);
                break;
            case self::JSON:
                $data = json_decode($value);
                break;
            case self::BASE64:
                $data = base64_decode($value);
                if ($data !== false) {
                    $data = @unserialize($data);
                }
                break;
        }

        return $data;
    }
}