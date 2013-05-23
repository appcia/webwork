<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Exception\Exception;

class Encoder
{
    const PHP = 1;
    const JSON = 2;
    const BASE64 = 3;

    /**
     * @var array
     */
    private static $encodingValues = array(
        self::PHP => 'php',
        self::JSON => 'json',
        self::BASE64 => 'base64'
    );

    /**
     * @var int
     */
    private $encoding;

    /**
     * Constructor
     *
     * @param int $encoding
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
     * @return Encoder
     * @throws Exception
     */
    public static function create($encoding)
    {
        if (!is_string($encoding)) {
            throw new Exception('Encoder cannot be created. Invalid argument specified');
        }

        return new self($encoding);
    }

    /**
     * @return array
     */
    public static function getEncodingValues()
    {
        return self::$encodingValues;
    }

    /**
     * @return int
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param int $encode
     *
     * @return Encoder
     * @throws Exception
     */
    public function setEncoding($encode)
    {
        if (!array_key_exists($encode, self::$encodingValues)) {
            throw new Exception(sprintf("Invalid encoding: '%s'", $encode));
        }

        $this->encoding = $encode;

        return $this;
    }

    /**
     * Code data
     *
     * @param array $value Value
     *
     * @return string
     * @throws Exception
     */
    public function code($value)
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
     * @return array
     * @throws Exception
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