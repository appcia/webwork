<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter\StripTags;

class StripTagsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function provideFiltering() {
        return array(
            array('', ''),
            array("<?php throw new Exception('Bad smell') ?>", ''),
            array('<p>Foo!</p>', 'Foo!'),
            array('<div><span>Bad markup</div></span>', 'Bad markup')
        );
    }

    /**
     * @param string $data Data to be cleared from tags
     * @param $expectedData
     *
     * @test
     * @dataProvider provideFiltering
     *
     * @return void
     */
    public function testFiltering($data, $expectedData) {
        $filter = new StripTags();

        $result = $filter->filter($data);

        $this->assertEquals($expectedData, $result, 'Stripping tags does not work correctly');
    }
}