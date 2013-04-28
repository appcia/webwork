<?

namespace Cms\Form\Auth;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\Date;
use Appcia\Webwork\Data\Validator\Email;
use Appcia\Webwork\Data\Validator\NotEmpty;
use Appcia\Webwork\Data\Validator\Same;
use Appcia\Webwork\Resource\Form;

class UserAdd extends Form {

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

        $passwordRepeat = new Field('passwordRepeat');
        $passwordRepeat->addValidator(new Same($password, $passwordRepeat));
        $this->addField($passwordRepeat);
        
        $nick = new Field('nick');
        $this->addField($nick);

        $name = new Field('name');
        $name->addValidator(new NotEmpty());
        $this->addField($name);

        $surname = new Field('surname');
        $surname->addValidator(new NotEmpty());
        $this->addField($surname);

        $birth = new Field('birth');
        $birth->addValidator(new NotEmpty());
        $birth->addValidator(new Date());
        $this->addField($birth);

        $group = new Field('group');
        $group->addValidator(new NotEmpty());
        $this->addField($group);

        $avatar = new Field('avatar', Field::FILE);
        $this->addField($avatar);

        return $this;
    }

}