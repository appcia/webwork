<?

namespace Cms\Form\Auth;

use Appcia\Webwork\Resource\Form;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\NotEmpty;

class GroupType extends Form {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $name = new Field('name');
        $name->addValidator(new NotEmpty());
        $this->addField($name);

        $description = new Field('description');
        $this->addField($description);

        $icon = new Field('icon', Field::FILE);
        $this->addField($icon);

        return $this;
    }

}