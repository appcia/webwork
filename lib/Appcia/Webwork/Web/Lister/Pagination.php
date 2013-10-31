<?

namespace Appcia\Webwork\Web\Lister;

use Appcia\Webwork\Web\Lister;

/**
 * Pagination helper
 */
class Pagination implements \IteratorAggregate
{
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
     *
     * @param Lister $lister
     */
    public function __construct(Lister $lister)
    {
        $this->lister = $lister;
        $this->pageNum = 1;
        $this->pagePer = 30;
    }

    /**
     * @return Lister
     */
    public function getLister()
    {
        return $this->lister;
    }

    /**
     * Get pages data
     *
     * @return array
     */
    public function getPages()
    {
        $count = $this->getPageCount();

        $pages = array();
        for ($p = 1; $p <= $count; $p++) {
            $pages[$p] = array(
                'first' => ($p == 1),
                'last' => ($p == $count),
                'current' => ($p == $this->pageNum)
            );
        }

        return $pages;
    }

    /**
     * Get page count
     *
     * @return int
     */
    public function getPageCount()
    {
        $total = $this->lister->getTotalCount();
        $count = (int) ($this->pagePer == 0)
            ? 0
            : ceil($total / $this->pagePer);

        return $count;
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
     * Get result offset
     *
     * @return int
     */
    public function getOffset()
    {
        $offset = ($this->pagePer == 0)
            ? 0
            : ($this->pageNum - 1) * $this->pagePer;

        return $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $pages = $this->getPages();

        return new \ArrayIterator($pages);
    }
}