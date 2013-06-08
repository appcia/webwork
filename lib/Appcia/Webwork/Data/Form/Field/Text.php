<?

namespace Appcia\Webwork\Data\Form\Field;

use Appcia\Webwork\Data\Filter;
use Appcia\Webwork\Data\Form\Field;

/**
 * Form field
 *
 * @package Appcia\Webwork\Data\Form\Field
 */
class Text extends Field
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->addFilter(new Filter\StripTags());
    }
}