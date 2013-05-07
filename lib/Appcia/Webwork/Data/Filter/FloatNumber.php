<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class FloatNumber extends Filter {

    /**
     * {@inheritdoc}
     */
    public function filter($value) {
        return floatval($value);
    }

}