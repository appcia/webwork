<?

namespace Appcia\Webwork\Data;

abstract class Validator {

    /**
     * Validate data
     *
     * @param mixed Data to be validated
     *
     * @return bool
     */
    abstract public function validate($data);

    /**
     * Get name simplified
     *
     * @return string
     */
    public function getName() {
        return lcfirst(substr(get_class($this), strlen(__CLASS__ . '\\')));
    }
}