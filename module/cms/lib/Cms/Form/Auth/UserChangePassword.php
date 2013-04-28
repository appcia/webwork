<?

namespace Cms\Form\Auth;

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\Email;
use Appcia\Webwork\Data\Validator\NotEmpty;
use Appcia\Webwork\Data\Validator\Same;

class UserChangePassword extends Form
{

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $passwordActual = new Field('passwordActual');
        $passwordActual->addValidator(new NotEmpty());
        $this->addField($passwordActual);

        $password = new Field('password');
        $password->addValidator(new NotEmpty());
        $this->addField($password);

        $passwordRepeat = new Field('passwordRepeat');
        $passwordRepeat->addValidator(new NotEmpty());
        $passwordRepeat->addValidator(new Same($password, $passwordRepeat));
        $this->addField($passwordRepeat);

        return $this;
    }

}