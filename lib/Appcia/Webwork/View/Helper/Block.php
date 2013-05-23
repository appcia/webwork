<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\Exception\Exception;

class Block extends Helper
{
    /**
     * Extending map
     *
     * @var array
     */
    private $extends;

    /**
     * Output buffer
     *
     * @var array
     */
    private $buffer;

    /**
     * Block data
     *
     * @var array
     */
    private $blocks;

    /**
     * Stack for block names
     *
     * @var array
     */
    private $stack;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->extends = array();
        $this->buffer = array();
        $this->stack = array();
        $this->blocks = array();
    }

    /**
     * Output block contents
     *
     * @param string $name Block name
     *
     * @return void
     * @throws Exception
     */
    public function block($name)
    {
        if (!isset($this->blocks[$name])) {
            throw new Exception(sprintf("Block '%s' does not exist", $name));
        }

        echo $this->blocks[$name];
    }

    /**
     * Begin block definition
     *
     * @param string $name Block name
     * @param string $file View to be extended
     *
     * @throws Exception
     */
    public function begin($name, $file = null)
    {
        if ($file !== null) {
            if (isset($this->extends[$name])) {
                throw new Exception(sprintf("Block name that will be extended is already used: '%s'", $name));
            }

            // Associate block with extending
            $this->extends[$name] = $file;
        }

        // Stop and save current output capturing
        $this->buffer[$name] = ob_get_clean();

        // Start capturing new block
        ob_start();

        array_push($this->stack, $name);
    }

    /**
     * End block definition
     *
     * @param $name
     *
     * @return mixed|string
     * @throws Exception
     */
    public function end($name = null)
    {
        // Retrieve block name from stack, check that match if specified
        $check = array_pop($this->stack);
        if ($name === null) {
            $name = $check;
        } else if ($name !== $check) {
            throw new Exception(sprintf("Block begin / end structure is not consistent." . PHP_EOL
                . "Problem occurred with: '%s'", $name));
        }

        // Get captured block
        $content = ob_get_clean();

        // Continue previous output capturing
        ob_start();

        echo $this->buffer[$name];
        unset($this->buffer[$name]);

        // Set block only when there is no previous
        if (empty($this->blocks[$name])) {
            $this->blocks[$name] = $content;
        }

        // Check that block must be extended
        if (isset($this->extends[$name])) {
            $file = $this->extends[$name];

            unset($this->extends[$name]);
            echo $this->getHelper('render')
                ->render($file);
        } else {
            $this->block($name);
        }
    }
}
