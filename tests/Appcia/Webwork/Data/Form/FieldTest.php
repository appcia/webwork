<?

namespace Appcia\Webwork\Data\Form;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\Email as EmailValidator;
use Appcia\Webwork\Data\Validator\NotEmpty as NotEmptyValidator;
use Appcia\Webwork\Data\Filter\StripTags as StripTagsFilter;
use Appcia\Webwork\Data\Filter\FloatNumber as FloatNumberFilter;

class FieldTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     *
     * @return void
     */
    public function testCreating() {
        $field = new Field('email');
        $this->assertEquals($field->getValue(), null, 'Field default value should be null');

        $field = new Field('email', 'foo');

        $value = $field->getValue();
        $this->assertEquals('foo', $value, 'Field values are not equal');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testEmptyValues() {
        $field = new Field('title');

        $valid = $field->validate();

        $this->assertTrue($valid, 'Field should be pass validation without any validators used');

        $value = $field->filter();

        $this->assertEquals($value, '', 'Field should be empty after filtering when default value passed');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testValidatorChain() {
        $field = new Field('email');

        $field->addValidator(new NotEmptyValidator());
        $field->addValidator(new EmailValidator());
        $field->setValue('foo@example.com');

        $valid = $field->validate();

        $this->assertTrue($valid, 'E-mail field does not pass validation chain');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testFilterChain() {
        $field = new Field('content');

        $field->addFilter(new StripTagsFilter());
        $field->addFilter(new FloatNumberFilter());
        $field->setValue('<time>2012-01-01</time> <strong>Hello Foo!</strong>');

        $value = $field->filter();

        $this->assertEquals($value, 2012, 'Content field does not pass validation chain');
    }
}