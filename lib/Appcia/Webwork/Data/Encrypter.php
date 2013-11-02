<?

namespace Appcia\Webwork\Data;

/**
 * Data encrypter
 */
class Encrypter
{
    /**
     * Methods
     */
    const SHA1 = 'sha1';
    const SHA256 = 'sha256';
    const MD5 = 'md5';
    const CRC32 = 'crc32';
    const TIGER128 = 'tiger128,3';

    /**
     * @var array
     */
    protected static $methods = array(
        self::SHA1,
        self::SHA256,
        self::MD5,
        self::CRC32,
        self::TIGER128
    );

    /**
     * Encryption method
     *
     * @var string
     */
    protected $method;

    /**
     * Salt
     *
     * @var string
     */
    protected $salt;

    /**
     * Constructor
     *
     * @param string      $method
     * @param string|null $salt
     */
    public function __construct($method = self::SHA256, $salt = null)
    {
        $this->setMethod($method);
        $this->setSalt($salt);
    }

    /**
     * Generate random salt
     *
     * @return string
     */
    public static function randSalt()
    {
        mt_srand(microtime(true) * 100000 + memory_get_usage(true));
        $salt = md5(uniqid(mt_rand(), true));

        return $salt;
    }

    /**
     * Get available methods
     *
     * @return array
     */
    public static function getMethods()
    {
        return self::$methods;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set method
     *
     * @param string $method
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMethod($method)
    {
        if (!in_array($method, self::$methods)) {
            throw new \InvalidArgumentException(sprintf("Encryption method '%s' is invalid or unsupported.", $method));
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Crypt data
     *
     * @param string      $value Value
     * @param string|null $salt  Salt
     *
     * @return string
     */
    public function crypt($value, $salt = null)
    {
        if ($salt === null) {
            $salt = $this->salt;
        }

        $data = $salt . $value;
        $value = hash($this->method, $data);

        return $value;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt Salt
     *
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }
}