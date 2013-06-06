<?

namespace Appcia\Webwork\Resource;

use Appcia\Webwork\Resource\Resource;

/**
 * Base for resource processor (for thumbnails, format derivatives)
 *
 * @package Appcia\Webwork\Resource
 */
abstract class Processor {

    /**
     * Manager
     *
     * @var Manager
     */
    private $manager;

    /**
     * Set manager
     *
     * @param Manager $manager
     *
     * @return $this
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Process and create derivative resources basing on original resource.
     * Should return array of produced types (optionally with their names as keys).
     *
     * Each resource key can be used in path mapping.
     *
     * @param Resource $resource Original resource
     * @param array    $settings Custom settings
     *
     * @return array
     */
    abstract public function process(Resource $resource, array $settings);

}