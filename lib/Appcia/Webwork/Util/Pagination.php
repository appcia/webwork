<?

namespace Appcia\Webwork\Util;

/**
 * Pagination helper
 *
 * @package Appcia\Webwork\Util
 */
abstract class Pagination
{
    /**
     * @var Lister
     */
    protected $lister;

    /**
     * @var int
     */
    protected $before;

    /**
     * @var int
     */
    protected $after;

    /**
     * @var boolean
     */
    protected $first;

    /**
     * @var boolean
     */
    protected $last;

    /**
     * Constructor
     *
     * @param Lister $lister
     */
    public function __construct(Lister $lister)
    {
        $this->lister = $lister;
        $this->before = 3;
        $this->after = 3;
        $this->first = true;
        $this->last= true;
    }

    /**
     * @param int $after
     *
     * @return $this
     */
    public function setAfter($after)
    {
        $this->after = (int) $after;

        return $this;
    }

    /**
     * @return int
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param int $before
     *
     * @return $this
     */
    public function setBefore($before)
    {
        $this->before = (int) $before;

        return $this;
    }

    /**
     * @return int
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * @param \Appcia\Webwork\Util\Lister $lister
     *
     * @return $this
     */
    public function setLister($lister)
    {
        $this->lister = $lister;

        return $this;
    }

    /**
     * @return \Appcia\Webwork\Util\Lister
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
        $pages = $this->lister->getPageCount();
        $current = $this->lister->getPageNum();

        $res = array();
        for ($p = 1; $p <= $pages; $p++) {
            $res[$p] = array(
                'first' => ($p == 1),
                'last' => ($p == $pages),
                'current' => ($p == $current)
            );
        }

        return $res;
    }
}