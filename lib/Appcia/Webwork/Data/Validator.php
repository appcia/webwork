<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Component;

abstract class Validator extends Component {

    /**
     * Validate data
     *
     * @param mixed Data to be validated
     *
     * @return bool
     */
    abstract public function validate($value);
}