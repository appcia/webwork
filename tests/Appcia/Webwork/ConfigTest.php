<?

namespace Appcia\Webwork;

use Appcia\Webwork\Route;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadingFromFile()
    {
        $config = new Config();
        $file = __DIR__ . '/ConfigFile.php';

        $config->loadFile($file);

        $actual = $config->toArray();
        $expected = array(
            'foo' => true,
            'bar' => 'Lorem ipsum dolor sit amet'
        );

        $this->assertSame($expected, $actual);
    }

    public function testExtending()
    {
        $config  = new Config(array(
            'foo' => 1,
            'bar' => 'lorem ipsum',
            'stick' => true,
        ));

        $anotherConfig = new Config(array(
            'foo' => 0,
            'bar' => 'ablar amis',
            'stick' => false,
            'glue' => 'strong'
        ));


        $actual = $config->extend($anotherConfig)->toArray();
        $expected = array(
            'foo' => 0,
            'bar' => 'ablar amis',
            'stick' => false,
            'glue' => 'strong'
        );

        $this->assertSame($expected, $actual);
    }

    public function testInjecting()
    {
        $config  = new Config(array(
            'name' => 'index',
            'path' => '/',
            'module' => 'cms',
            'controller' => 'page',
            'action' => 'home',
            'unused' => 'param'
        ));

        $route = new Route();
        $config->inject($route);

        $this->assertEquals('index', $route->getName());
        $this->assertEquals('/', $route->getPath());
        $this->assertEquals('cms', $route->getModule());
        $this->assertEquals('page', $route->getController());
        $this->assertEquals('home', $route->getAction());
    }

}