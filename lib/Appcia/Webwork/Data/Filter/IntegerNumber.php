<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class IntegerNumber extends Filter {

    /**
     * {@inheritdoc}
     */
    public function filter($value) {
        return intval($value);
    }

}