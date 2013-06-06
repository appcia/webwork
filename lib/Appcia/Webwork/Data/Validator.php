<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Component;

/**
 * Base for data validators
 *
 * @package Appcia\Webwork\Data
 */
abstract class Validator extends Component {

    /**
     * Validate data
     *
     * @param mixed $value Data to be validated
     *
     * @return boolean
     */
    abstract public function validate($value);
}