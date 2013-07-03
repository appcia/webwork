<?

namespace Appcia\Webwork\Data\Form\Field;

use Appcia\Webwork\Data\Form\Field;

/**
 * Field with plain data
 *
 * @package Appcia\Webwork\Data\Form\Field
 */
class Plain extends Field
{
    /**
     * @return $this
     * @throws \ErrorException
     */
    public function prepare()
    {
        parent::prepare();

        if (empty($this->filters)) {
            throw new \ErrorException(sprintf("Plain field '%s' does not have any filters." . PHP_EOL
            . " Add filter that will provide appropriate protection against CSRF attacks.", $this->name));
        }

        return $this;
    }
}