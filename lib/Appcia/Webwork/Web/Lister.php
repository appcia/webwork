<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Web\Lister\Filters;
use Appcia\Webwork\Web\Lister\Pagination;
use Appcia\Webwork\Web\Lister\Sorters;

/**
 * Listing helper
 */
abstract class Lister implements \IteratorAggregate, \Countable
{
    /**
     * Filters applied
     *
     * @var Filters
     */
    protected $filters;

    /**
     * Sorters applied
     *
     * @var Sorters
     */
    protected $sorters;

    /**
     * Pagination helper
     *
     * @var Pagination
     */
    protected $pagination;

    /**
     * Fetched elements
     *
     * @var array
     */
    protected $elements;

    /**
     * Total element count
     *
     * @var int
     */
    protected $totalCount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pagination = new Pagination($this);
        $this->filters = new Filters();
        $this->sorters = new Sorters();
        $this->elements = null;
        $this->totalCount = null;
    }

    /**
     * Get total element count
     *
     * @return int
     */
    public function getTotalCount()
    {
        if ($this->totalCount === null) {
            $this->totalCount = $this->countElements();
        }

        return $this->totalCount;
    }

    /**
     * Get total elements count
     *
     * @return int
     */
    abstract public function countElements();

    /**
     * Get current element count
     *
     * @return int
     */
    public function getCount()
    {
        $elements = $this->getElements();
        $count = count($elements);

        return $count;
    }

    /**
     * Get lazy loaded elements
     *
     * @return array
     */
    public function getElements()
    {
        if ($this->elements === null) {
            $this->elements = $this->fetchElements();
        }

        return $this->elements;
    }

    /**
     * Fetch elements from database or other data source
     *
     * @return mixed
     */
    abstract public function fetchElements();

    /**
     * @return Filters
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Filters $filters
     *
     * @return $this
     */
    public function setFilters(Filters $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return Pagination
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * @param Pagination $pagination
     *
     * @return $this
     */
    public function setPagination(Pagination $pagination)
    {
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * @return Sorters
     */
    public function getSorters()
    {
        return $this->sorters;
    }

    /**
     * @param Sorters $sorters
     *
     * @return $this
     */
    public function setSorters(Sorters $sorters)
    {
        $this->sorters = $sorters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getElements());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCount();
    }
}