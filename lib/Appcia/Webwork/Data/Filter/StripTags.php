<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class StripTags extends Filter
{
    /**
     * @var string
     */
    private $allowedTags;

    /**
     * Constructor
     */
    public function __construct(array $allowed = array())
    {
        $this->allowedTags = $this->mergeTags($allowed);
    }

    /**
     * Prepare correct tags format
     *
     * @param array $tags Allowed tags
     *
     * @return string
     */
    private function mergeTags(array $tags)
    {
        $value = '';
        foreach ($tags as $tag) {
            $value .= '<' . $tag . '>';
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $value = strip_tags($value, $this->allowedTags);

        return $value;
    }

}