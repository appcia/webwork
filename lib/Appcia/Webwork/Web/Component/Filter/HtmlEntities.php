<?

namespace Appcia\Webwork\Web\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Web\Context;

/**
 * Class HtmlEntities
 */
class HtmlEntities extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $html = null;
        switch ($this->context->getHtmlVersion()) {
            case Context::HTML_401:
                $html = ENT_HTML401;
                break;
            case Context::HTML_5:
            default:
                $html = ENT_HTML5;
                break;
        }

        $result = htmlentities($value, ENT_COMPAT | $html, $this->context->getCharset());

        return $result;
    }
}