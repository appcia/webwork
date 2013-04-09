<?

namespace Appcia\Webwork\Data;

class Component {

    /**
     * Get name by class name (without namespace)
     *
     * @return string
     */
    public function getName() {
        return lcfirst(substr(get_class($this), strlen(__CLASS__ . '\\')));
    }

}