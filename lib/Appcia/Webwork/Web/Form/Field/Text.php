<?

namespace Appcia\Webwork\Web\Form\Field;

use Appcia\Webwork\Web\Component\Filter;
use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Web\Form;

/**
 * Form field
 *
 * @package Appcia\Webwork\Web\Form\Field
 */
class Text extends Field
{
    /**
     * @{@inheritdoc}
     */
    public function __construct(Form $form, $name)
    {
        parent::__construct($form, $name);

        $this->addFilter(new Filter\StripTags($form->getContext()));
    }
}