<?

namespace Appcia\Webwork\System;

class Php
{
    /**
     * PHP binary
     *
     * @var File
     */
    private $bin;

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
     * Set PHP binary
     *
     * @param string $bin
     *
     * @return Php
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
     * Get PHP binary
     *
     * @return File
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Check file syntax
     *
     * @param string $file Path
     *
     * @return bool
     */
    public function checkSyntax($file)
    {
        $args = sprintf('-l %s', $file);
        $data = $this->bin->execute($args);
        $check = ($data['code'] === 0);

        return $check;
    }
}