<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;
use SimpleXMLElement;

class Xml extends Renderer
{
    /**
     * Root node
     *
     * @var string
     */
    private $root;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->root = 'root';
    }

    /**
     * {@inheritdoc}
     */
    public function render($template = null)
    {
        $data = $this->getView()
            ->getData();

        $xml = new SimpleXMLElement('<' . $this->root . '/>');
        $this->generateXml($data, $xml);

        $content = $xml->asXML();

        return $content;
    }

    /**
     * Recursive helper for XML generation
     *
     * @param array            $data Data
     * @param SimpleXMLElement $xml  Node
     *
     * @return void
     */
    private function generateXml($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subNode = $xml->addChild("$key");
                    $this->generateXml($value, $subNode);
                } else {
                    $this->generateXml($value, $xml);
                }
            } else {
                $xml->addChild("$key", "$value");
            }
        }
    }

    /**
     * Set root node
     *
     * @param string $root
     *
     * @return Xml
     * @throws Exception
     */
    public function setRoot($root)
    {
        if (empty($root)) {
            throw new Exception('Root node cannot be empty');
        }

        $this->root = $root;

        return $this;
    }

    /**
     * Get root node
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
}