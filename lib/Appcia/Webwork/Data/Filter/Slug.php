<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class Slug extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        $charset = $this->getContext()
            ->getCharset();

        $value = iconv($charset, 'ASCII//TRANSLIT', $value);
        $value = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $value);
        $value = strtolower(trim($value, '-'));
        $value = preg_replace("/[\/_| -]+/", '-', $value);

        return $value;
    }
}