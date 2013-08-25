<?

namespace Appcia\Webwork\Data\Component;

use Appcia\Webwork\Data\Component;

/**
 * Base for data filter
 */
abstract class Filter extends Component {

    /**
     * Filter data
     *
     * @param mixed $value Data to be filtered
     *
     * @return boolean
     */
    abstract public function filter($value);

}