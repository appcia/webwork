<?

namespace Appcia\Webwork\Data\Validator;

use Appcia\Webwork\Data\Validator\NotEmpty;

class NotEmptyTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function provideValidating() {
        return array(
            array('', false),
            array(0, false),
            array('0', false),
            array(0.0, false),
            array('0.0', false),
            array(1, true),
            array('1', true),
            array('Foo', true),
            array('123 XXX', true)
        );
    }

    /**
     * @param string $value            Value to be checked
     * @param bool   $validationPassed Validation status
     *
     * @test
     * @dataProvider provideValidating
     *
     * @return void
     */
    public function testValidating($value, $validationPassed) {
        $validator = new NotEmpty();

        $result = $validator->validate($value);

        $this->assertEquals($validationPassed, $result, 'Not empty validator does not work properly');
    }
}