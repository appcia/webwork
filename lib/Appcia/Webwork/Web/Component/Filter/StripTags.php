<?

namespace Appcia\Webwork\Web\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Web\Context;

class StripTags extends Filter
{
    /**
     * @var string
     */
    protected $allowedTags;

    /**
     * Constructor
     *
     * @param Context $context Use context
     * @param array   $allowed Allowed tag names
     */
    public function __construct(Context $context, array $allowed = array())
    {
        parent::__construct($context);

        $this->allowedTags = $this->mergeTags($allowed);
    }

    /**
     * Prepare correct tags format
     *
     * @param array $tags Allowed tags
     *
     * @return string
     */
    protected function mergeTags(array $tags)
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