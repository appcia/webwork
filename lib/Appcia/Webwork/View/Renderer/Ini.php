<?

namespace Appcia\Webwork\View\Renderer;

use Appcia\Webwork\View\Helper;
use Appcia\Webwork\View\Renderer;

/**
 * INI view renderer
 *
 * @package Appcia\Webwork\View\Renderer
 */
class Ini extends Renderer
{
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
     * @throws \InvalidArgumentException
     */
    protected function processData(array &$data)
    {
        $max = null;
        foreach ($data as $value) {
            $depth = $this->calculateDepth($value) + 1;

            if ($max === null) {
                $max = $depth;
            } elseif ($depth !== $max) {
                throw new \InvalidArgumentException(
                    'Rendering INI format requires array in which all elements have with same complexity.'
                );
            }

        }

        $max = (int) $max;
        if ($max > 2) {
            throw new \InvalidArgumentException('Rendering INI format expects only 1 or 2 dimensional array.');
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
    protected function calculateDepth($data)
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
     * @param array   $data     Associative array
     * @param boolean $sections Create sections
     *
     * @return string
     */
    protected function generateIni($data, $sections = false)
    {
        $content = '';

        if ($sections) {
            foreach ($data as $key => $elem) {
                $content .= "[" . $key . "]" . PHP_EOL;

                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = \"" . $elem2[$i] . "\"" . PHP_EOL;
                        }
                    } elseif ($elem2 == "") {
                        $content .= $key2 . " = " . PHP_EOL;
                    } else {
                        $content .= $key2 . " = \"" . $elem2 . "\"" . PHP_EOL;
                    }
                }
            }
        } else {
            foreach ($data as $key => $elem) {
                if (is_array($elem)) {
                    foreach ($elem as $val) {
                        $content .= $key . "[] = \"" . $val . "\"" . PHP_EOL;
                    }
                } elseif ($elem == "") {
                    $content .= $key . " = " . PHP_EOL;
                } else {
                    $content .= $key . " = \"" . $elem . "\"" . PHP_EOL;
                }
            }
        }

        return $content;
    }
}