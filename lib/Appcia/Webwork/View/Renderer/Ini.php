<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\Exception;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

class Ini extends Renderer
{
    /**
     * @var string
     */
    private $newLine;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->newLine = PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template = null)
    {
        $data = $this->getView()
            ->getData();

        $depth = $this->processData($data);
        $sections = ($depth === 2);
        $content = $this->generateIni($data, $sections);

        return $content;
    }

    /**
     * Process data
     *
     * @param array $data Data
     *
     * @return int
     * @throws Exception
     */
    private function processData(array &$data)
    {
        $max = null;
        foreach ($data as $value) {
            $depth = $this->calculateDepth($value) + 1;

            if ($max === null) {
                $max = $depth;
            } elseif ($depth !== $max) {
                throw new Exception('Rendering INI format requires array with same complexity');
            }

        }

        $max = (int) $max;
        if ($max > 2) {
            throw new Exception('Rendering INI format expects only 1 or 2 dimensional array');
        }

        return $max;
    }

    /**
     * Calculate data depth
     *
     * @param array $data Data
     *
     * @return int
     */
    private function calculateDepth($data)
    {
        if (!is_array($data)) {
            return 0;
        }

        $max = 1;
        foreach ($data as $value) {
            if (is_array($value)) {
                $depth = $this->calculateDepth($value) + 1;

                if ($depth > $max) {
                    $max = $depth;
                }
            }
        }

        return $max;
    }

    /**
     * Generate configuration in INI format
     *
     * @param array $data     Associative array
     * @param bool  $sections Create sections
     *
     * @return string
     */
    private function generateIni($data, $sections = false)
    {
        $content = "";
        $nl = PHP_EOL;

        if ($sections) {
            foreach ($data as $key => $elem) {
                $content .= "[" . $key . "]" . $nl;

                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = \"" . $elem2[$i] . "\"" . $nl;
                        }
                    } elseif ($elem2 == "") {
                        $content .= $key2 . " = " . $nl;
                    } else {
                        $content .= $key2 . " = \"" . $elem2 . "\"" . $nl;
                    }
                }
            }
        } else {
            foreach ($data as $key => $elem) {
                if (is_array($elem)) {
                    foreach ($elem as $val) {
                        $content .= $key . "[] = \"" . $val . "\"" . $nl;
                    }
                } elseif ($elem == "") {
                    $content .= $key . " = " . $nl;
                } else {
                    $content .= $key . " = \"" . $elem . "\"" . $nl;
                }
            }
        }

        return $content;
    }

    /**
     * Set new line character
     *
     * @param string $newLine
     *
     * @return Ini
     */
    public function setNewLine($newLine)
    {
        $this->newLine = $newLine;

        return $this;
    }

    /**
     * Get new line character
     *
     * @return string
     */
    public function getNewLine()
    {
        return $this->newLine;
    }
}