<?

namespace Appcia\Webwork\Data;

abstract class Filter {

    /**
     * Filter data
     *
     * @param mixed $data Data to be filtered
     *
     * @return bool
     */
    abstract public function filter($data);

    /**
     * Get name simplified
     *
     * @return string
     */
    public function getName() {
        return lcfirst(substr(get_class($this), strlen(__CLASS__ . '\\')));
    }
}