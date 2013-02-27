<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Data\Form\Field;

class DerivedForm extends Form
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $content = new Field('content');

        $this->addField($content);

        return $this;
    }
}

?>