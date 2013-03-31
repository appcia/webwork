<?

namespace Appcia\Webwork\Data\Filter;

use Appcia\Webwork\Data\Filter;

class File extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter($data)
    {
        // Trim empty values to empty array
        if (empty($data['tmp_name'])) {
            return array();
        }

        // Normalize multi upload
        if (is_array($data['tmp_name'])) {
            $result = array();

            foreach ($data as $key => $all) {
                foreach ($all as $i => $val) {
                    $result[$i][$key] = $val;
                }
            }

            return $result;
        }

        return $data;
    }

}