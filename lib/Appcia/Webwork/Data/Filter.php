<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Component;

abstract class Filter extends Component {

    /**
     * Filter data
     *
     * @param mixed $value Data to be filtered
     *
     * @return bool
     */
    abstract public function filter($value);

}