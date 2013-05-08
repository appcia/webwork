<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;
use Appcia\Webwork\Exception;

class Cut extends Filter
{
    /**
     * Constructor
     *
     * @param int $start  Characters to be cut from the start (could be negative - numbered from end)
     * @param int $length String piece length
     *
     * @throws Exception
     */
    public function __construct($start, $length)
    {
        if ($length < 0) {
            throw new Exception('Cut length cannot be a negative number');
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