<?

namespace Appcia\Webwork\Util;

/**
 * Listing helper
 *
 * @package Appcia\Webwork\Util
 */
abstract class Lister
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $sorters;

    /**
     * @var int
     */
    protected $pagePer;

    /**
     * @var int
     */
    protected $pageNum;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filters = array();
        $this->sorters = array();
        $this->pagePer = NULL;
        $this->pageNum = 1;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters($filters)
    {
        $this->clearFilters();
        foreach ($filters as $name => $value) {
            $this->addFilter($name, $value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearFilters()
    {
        $this->filters = array();

        return $this;
    }

    public function addFilter($name, $value)
    {
        $this->filters[$name] = (string) $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageNum()
    {
        return $this->pageNum;
    }

    /**
     * @param mixed $pageNum
     *
     * @return $this
     */
    public function setPageNum($pageNum)
    {
        $this->pageNum = max(1, $pageNum);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPagePer()
    {
        return $this->pagePer;
    }

    /**
     * @param int $pagePer
     *
     * @return $this
     */
    public function setPagePer($pagePer)
    {
        $this->pagePer = max(0, $pagePer);

        return $this;
    }

    /**
     * @return array
     */
    public function getSorters()
    {
        return $this->sorters;
    }

    /**
     * @param array $sorters
     *
     * @return $this
     */
    public function setSorters($sorters)
    {
        $this->clearSorters();
        foreach ($sorters as $name => $value) {
            $this->addSorter($name, $value);
        }

        return $this;
    }

    /**
     * Clear sorter values
     *
     * @return $this
     */
    public function clearSorters()
    {
        $this->sorters = array();

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addSorter($name, $value)
    {
        $this->sorters[$name] = (string) $value;

        return $this;
    }

    /**
     * Fetch results from database or other data source
     *
     * @return mixed
     */
    abstract public function getResult();

    /**
     * Get result offset
     *
     * @return int
     */
    public function getOffset()
    {
        $offset = $this->pagePer == 0
            ? 0
            : ($this->pageNum - 1) * $this->pagePer;

        return $offset;
    }
}