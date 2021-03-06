<?

namespace Appcia\Webwork\Web\Lister;

use Appcia\Webwork\Core\Object;
use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Web\Lister;

class Option implements Object
{
    /**
     * Sorting directions
     */
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * Name (could be a database column)
     *
     * @var string
     */
    protected $name;

    /**
     * Displayable name
     *
     * @var string
     */
    protected $label;

    /**
     * Value for filtering
     *
     * @var string|null
     */
    protected $filter;

    /**
     * Value for sorting
     *
     * @var string|null
     */
    protected $dir;

    /**
     * Constructor
     *
     * @param string $name Name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->label = ucfirst($name);
    }

    /**
     * {@inheritdoc}
     */
    public static function objectify($data, $args = array())
    {
        return Objector::objectify($data, $args, get_called_class());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param null|string $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param null|string $dir
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDir($dir)
    {
        if ($dir !== null && !array_key_exists($dir, static::getDirs())) {
            throw new \InvalidArgumentException(sprintf("Direction for lister option '%s' is invalid.", $this->name));
        }

        $this->dir = $dir;

        return $this;
    }

    /**
     * @return array
     */
    public static function getDirs()
    {
        return array(
            static::ASC => 'ascending',
            static::DESC => 'descending'
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->label;
    }
}