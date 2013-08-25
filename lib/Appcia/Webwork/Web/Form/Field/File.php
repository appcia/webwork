<?

namespace Appcia\Webwork\Web\Form\Field;

use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Resource\Resource;

/**
 * Field with uploaded file data
 *
 * @package Appcia\Webwork\Web\Form\Field
 */
class File extends Field
{
    /**
     * Check whether field contains existing resource
     *
     * @return bool
     */
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