<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Component;

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