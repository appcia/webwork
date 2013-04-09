<?

namespace Appcia\Webwork\Data;

abstract class Filter extends Component {

    /**
     * Filter data
     *
     * @param mixed $data Data to be filtered
     *
     * @return bool
     */
    abstract public function filter($data);

}