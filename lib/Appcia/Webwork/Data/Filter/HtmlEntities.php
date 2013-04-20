<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Context;
use Appcia\Webwork\Data\Filter;

class HtmlEntities extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($data)
    {
        $html = null;
        switch ($this->getContext()->getHtmlVersion()) {
            case Context::HTML_401:
                $html = ENT_HTML401;
                break;
            case Context::HTML_5:
            default:
                $html = ENT_HTML5;
                break;
        }

        $charset = $this->getContext()
            ->getCharset();

        $result = htmlentities($data, ENT_COMPAT | $html, $charset);

        return $result;
    }
}