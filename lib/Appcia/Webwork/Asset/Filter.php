<?

namespace Appcia\Webwork\Asset;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;

abstract class Filter implements Object
{
    /**
     * Prepare file, change filename, extension etc
     *
     * @param Asset $asset
     * @return $this
     */
    abstract public function prepare(Asset $asset);

    /**
     * Filter content
     *
     * @param Asset $asset
     *
     * @return mixed
     */
    abstract public function filter(Asset $asset);

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
    }
}