<?

namespace Appcia\Webwork\Asset;

use Appcia\Webwork\System\File;

class Asset
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var File
     */
    protected $source;

    /**
     * @var File
     */
    protected $target;

    /**
     * @var Filter[]
     */
    protected $filters;

    /**
     * Content
     *
     * @var string|null
     */
    protected $content;

    /**
     * Do not regenerate if debug
     *
     * @var boolean
     */
    protected $lazy;

    /**
     * Constructor
     *
     * @param Manager $manager
     * @param string  $source
     */
    public function __construct(Manager $manager, $source)
    {
        if (!$source instanceof File) {
            $source = new File($source);
        }

        $this->filters = array();
        $this->lazy = false;

        $this->manager = $manager;
        $this->source = $source;
        $this->target = $this->manager->getDir()
            ->hashFile($this->source);
    }

    /**
     * Generate only if file does not exist (omit debug)
     *
     * @param bool $flag
     * @return $this
     */
    public function laze($flag = true)
    {
        $this->lazy = (bool) $flag;

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
     * Get filters associated with
     *
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Filter[] $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return File
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get processed file path
     *
     * @return string
     */
    public function __toString()
    {
        $this->cache();

        return (string) $this->target;
    }

    /**
     * Cache file (do not regenerate on every call)
     *
     * @return $this
     */
    public function cache()
    {
        $this->prepare();
        if (!$this->target->exists() || (!$this->lazy && $this->manager->isDebug())) {
            $this->write();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function write()
    {
        $this->filter();
        $this->target->getDir()
            ->create();
        $this->target->write($this->content);

        return $this;
    }

    /**
     * Prepare before using filters
     *
     * @return $this
     */
    protected function prepare()
    {
        foreach ($this->filters as $filter) {
            $filter->prepare($this);
        }

        return $this;
    }

    /**
     * Apply filters
     *
     * @return $this
     */
    protected function filter()
    {
        if (empty($this->filters)) {
            $this->filters[] = $this->manager->getFilter($this->source->getExtension());
        }

        $this->content = $this->getSource()
            ->read();
        foreach ($this->filters as $filter) {
            $filter->filter($this);
        }

        return $this->content;
    }

    /**
     * Get file
     *
     * @return File
     */
    public function getSource()
    {
        return $this->source;
    }
}