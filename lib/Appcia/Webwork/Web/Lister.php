<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Lister\Option;
use Appcia\Webwork\Web\Lister\Pagination;

/**
 * Listing helper
 */
abstract class Lister implements \IteratorAggregate, \Countable
{
    /**
     * Customizable options
     *
     * @var Option[]
     */
    protected $options;

    /**
     * Pagination
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
        $this->filters = array();
        $this->sorters = array();
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
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Option[] $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array();
        foreach ($options as $name => $option) {
            if (!$option instanceof Option) {
                $option = Option::objectify($option, array($name));
            }

            $this->options[$name] = $option;
        }

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
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getElements());
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
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCount();
    }

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
     * Get filter value
     * If option not specified, returns first option filter value
     *
     * @param string|null $option Option name
     *
     * @return string|null
     */
    public function getFilter($option = null)
    {
        $filters = $this->getFilters();
        $value = $this->getValue($option, $filters);

        return $value;
    }

    /**
     * Get values from all active filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        foreach ($this->options as $option) {
            if ($option->getFilter() !== null) {
                $filters[$option->getName()] = $option->getFilter();
            }
        }

        return $filters;
    }

    /**
     * Helper function for getting option value
     *
     * @param string|null $option Option name
     * @param array       $values Possible values
     *
     * @return null
     */
    protected function getValue($option = null, array $values)
    {
        if ($option === null) {
            if (empty($values)) {
                return null;
            } else {
                list($option) = array_keys($values);
            }
        } elseif ($option instanceof Option) {
            $option = $option->getName();
        }

        $value = isset($values[$option])
            ? $values[$option]
            : null;

        return $value;
    }

    /**
     * Get sorter direction
     * If option not specified, returns first sorter direction
     *
     * @param string|null $option Option name
     *
     * @return string|null
     */
    public function getSorter($option = null)
    {
        $sorters = $this->getSorters();
        $dir = $this->getValue($option, $sorters);

        return $dir;
    }

    /**
     * Get directions from all active sorters
     *
     * @return array
     */
    public function getSorters()
    {
        $sorters = array();
        foreach ($this->options as $option) {
            if ($option->getDir() !== null) {
                $sorters[$option->getName()] = $option->getDir();
            }
        }

        return $sorters;
    }

    /**
     * Setup before fetching elements
     * Override if predefined keys must be changed
     *
     * @param array $data Data from request (GET, POST, session ...)
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function populate($data)
    {
        if (!empty($data['page'])) {
            $this->pagination->setPageNum($data['page']);
        }

        if (!empty($data['per-page'])) {
            $this->pagination->setPerPage($data['per-page']);
        }

        if (!empty($data['filter-option']) && !empty($data['filter-value'])) {
            $option = $this->getOption($data['filter-option']);
            $option->setFilter($data['filter-value']);
        }

        if (!empty($data['sorter-option']) && !empty($data['sorter-dir'])) {
            $option = $this->getOption($data['sorter-option']);
            $option->setDir($data['sorter-dir']);
        }

        return $this;
    }

    /**
     * Get option by name
     *
     * @param string $option Option name
     *
     * @return Option
     * @throws \InvalidArgumentException
     */
    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            throw new \InvalidArgumentException(sprintf("Lister option '%s' does not exist.", $option));
        }

        return $this->options[$option];
    }
}