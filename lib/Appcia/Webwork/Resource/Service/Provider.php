<?

namespace Appcia\Webwork\Resource\Service;

use Appcia\Webwork\Resource\Service;
use Appcia\Webwork\System\File;

/**
 * Provider for creating base resources (from archive files etc)
 */
abstract class Provider extends Service
{
    /**
     * @return File[]
     */
    public function run()
    {
        return parent::run();
    }
}