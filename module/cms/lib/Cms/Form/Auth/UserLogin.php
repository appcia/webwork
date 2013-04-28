<?

namespace Cms\Form\Auth;

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\Email;
use Appcia\Webwork\Data\Validator\NotEmpty;

class UserLogin extends Form {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $email = new Field('email');
        $email->addValidator(new NotEmpty());
        $email->addValidator(new Email());
        $this->addField($email);

        $password = new Field('password');
        $password->addValidator(new NotEmpty());
        $this->addField($password);

        return $this;
    }

}