<?

namespace Appcia\Webwork\Data\Form\Field;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Resource\Resource;

/**
 * Field with uploaded file data
 *
 * @package Appcia\Webwork\Data\Form\Field
 */
class File extends Field
{
    public function isResource()
    {
        if (!$this->value instanceof Resource) {
            return false;
        }

        $resource = $this->value;
        $flag = $resource->exists();

        return $flag;
    }
}