<?

namespace Appcia\Webwork\Data\Component\Filter;

use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Web\Context;

class Cut extends Filter
{
    /**
     * Constructor
     *
     * @param Context $context Use context
     * @param int     $start   Characters to be cut from the start (could be negative - numbered from end)
     * @param int     $length  String piece length
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Context $context, $start, $length)
    {
        parent::__construct($context);

        if ($length < 0) {
            throw new \InvalidArgumentException('Cut length cannot be a negative number');
        }

        $this->start = (int) $start;
        $this->length = (int) $length;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $value = mb_substr($value, $this->start, $this->length);

        return $value;
    }
}