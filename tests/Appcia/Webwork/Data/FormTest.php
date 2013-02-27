<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Data\Form;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Data\Validator\NotEmpty as NotEmptyValidator;
use Appcia\Webwork\Data\Filter\StripTags as StripTagsFilter;

use Appcia\Webwork\Data\DerivedForm;

class FormTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     *
     * @return void
     */
    public function testManuallyBuilding()
    {
        $form = new Form();

        $content = new Field('content');
        $form->addField($content);

        $this->assertTrue($form->hasField('content'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function testDerivedBuilding()
    {
        $form = new DerivedForm();

        $this->assertTrue($form->hasField('content'));
    }

    /**
     * @return array
     */
    public function provideProcessing()
    {
        return array(
            array(
                array(
                    'name' => '',
                    'content' => ''
                ),
                array(
                    'name' => '',
                    'content' => ''
                ),
                false
            ),
            array(
                array(
                    'name' => '',
                    'content' => '<p>Here I am!</p>'
                ),
                array(
                    'name' => '',
                    'content' => 'Here I am!'
                ),
                false
            ),
            array(
                array(
                    'name' => 'Foo bar',
                    'content' => '<p>Here I am!</p>'
                ),
                array(
                    'name' => 'Foo bar',
                    'content' => 'Here I am!'
                ),
                true
            )
        );
    }
    /**
     * @param array $inputData        Input data
     * @param array $processedData    Output data
     * @param bool  $validationPassed Validation flag
     *
     * @dataProvider provideProcessing
     *
     * @return void
     */
    public function testProcessing($inputData, $processedData, $validationPassed)
    {
        $form = new Form();

        $name = new Field('name');
        $name->addValidator(new NotEmptyValidator());

        $form->addField($name);

        $content = new Field('content');
        $content->addFilter(new StripTagsFilter());

        $form->addField($content);

        $form->populate($inputData);

        $valid = $form->process();
        $inputData = $form->getAll();

        $this->assertEquals($processedData, $inputData);
        $this->assertEquals($validationPassed, $valid);
    }
}