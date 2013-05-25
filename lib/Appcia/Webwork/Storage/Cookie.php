<?

namespace Appcia\Webwork\Storage;

use Appcia\Webwork\Data\Encoder;
use Appcia\Webwork\Exception\Exception;

/**
 * Cookie representation
 *
 * @todo Not implemented yet!
 * @package Appcia\Webwork\Storage
 */
class Cookie
{
    /**
     * Data serializer
     */
    private $encoder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->encoder = new Encoder();
    }

    /**
     * Get data encoder
     *
     * @return Encoder|null
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Set data encoder
     *
     * @param Encoder|string $encoder
     */
    public function setEncoder($encoder)
    {
        if (!$encoder instanceof Encoder) {
            $encoder = Encoder::create($encoder);
        }

        $this->encoder = $encoder;
    }
}