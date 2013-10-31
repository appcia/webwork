<?

namespace Appcia\Webwork\Web\Lister;

use Appcia\Webwork\Core\Data;

abstract class Options extends Data {

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->clearOptions();
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearOptions()
    {
        $this->data = array();

        return $this;
    }

    public function setOption($name, $value)
    {
        $this->data[$name] = (string) $value;

        return $this;
    }
}