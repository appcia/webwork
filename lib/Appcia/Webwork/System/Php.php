<?

namespace Appcia\Webwork\System;

/**
 * Wrapper for PHP binary
 *
 * Useful for checking file syntax
 *
 * @package Appcia\Webwork\System
 */
class Php
{
    /**
     * PHP binary
     *
     * @var File
     */
    protected $bin;

    /**
     * Constructor
     *
     * @param string $bin Path to PHP binary
     */
    public function __construct($bin = null)
    {
        if ($bin === null) {
            $bin = $this->findBin();
        }

        $this->setBin($bin);
    }

    /**
     * Retrieve PHP binary
     *
     * @return null|string
     */
    public static function findBin()
    {
        if (defined('PHP_BINARY') && PHP_BINARY != '') {
            return PHP_BINARY;
        } else if (file_exists('/usr/bin/php')) {
            return '/usr/bin/php';
        }

        return null;
    }

    /**
     * Set property (only runtime)
     *
     * @param string $key   Property name
     * @param string $value Value
     *
     * @throws \OutOfBoundsException
     */
    public static function set($key, $value)
    {
        if (ini_set($key, $value) === false) {
            throw new \OutOfBoundsException(sprintf(
                "Cannot set PHP property. '%s' is invalid or unsupported in current PHP version (%s).",
                $key,
                PHP_VERSION
            ));
        }
    }

    /**
     * Get property
     *
     * @param string $key Property name
     *
     * @return string
     * @throws \OutOfBoundsException
     */
    public static function get($key)
    {
        $value = ini_get($key);
        if ($value === false) {
            throw new \OutOfBoundsException(sprintf(
                "Cannot get PHP property. '%s' is invalid or unsupported in current PHP version (%s).",
                $key,
                PHP_VERSION
            ));
        }

        return $value;
    }

    /**
     * Get PHP binary
     *
     * @return File
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Set PHP binary
     *
     * @param string $bin
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setBin($bin)
    {
        if (empty($bin)) {
            throw new \InvalidArgumentException('PHP binary is not specified');
        }

        $this->bin = new File($bin);

        return $this;
    }

    /**
     * Check file syntax
     *
     * @param string $file Path
     *
     * @return boolean
     */
    public function checkSyntax($file)
    {
        $args = sprintf('-l %s', $file);
        $data = $this->bin->execute($args);
        $check = ($data['code'] === 0);

        return $check;
    }
}