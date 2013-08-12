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
        $this->first = true;
        $this->last= true;
    }

    /**
     * @param Lister $lister
     *
     * @return $this
     */
    public function setLister($lister)
    {
        $this->lister = $lister;

        return $this;
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