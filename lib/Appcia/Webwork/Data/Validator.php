<?

namespace Appcia\Webwork\Data;

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