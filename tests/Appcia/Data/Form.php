<?

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Data\Form\Field;

class FormTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testCreating() {
        $form = new Form();
    }
}