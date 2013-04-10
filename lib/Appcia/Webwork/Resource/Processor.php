<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Resource;

abstract class Processor {

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Process and create derivative resources basing on original resource
     * Should return array of produced types (optionally with their names as keys)
     *
     * Each resource key can be used in path mapping
     *
     * @param Resource $resource  Original resource
     * @param array    $settings  Custom settings
     *
     * @return array
     */
    abstract public function process(Resource $resource, array $settings);

}