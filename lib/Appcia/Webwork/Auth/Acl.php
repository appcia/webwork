<?

namespace Appcia\Webwork\Auth;

use Appcia\Webwork\Exception\Exception;
use Appcia\Webwork\Routing\Route;
use Appcia\Webwork\Storage\Session\Space;
use Psr\Log\InvalidArgumentException;

/**
 * Authorization with access control list based on route names and access groups
 *
 * @package Appcia\Webwork\Auth
 */
class Acl extends Auth
{
    const WILDCARD = '*';

    const ALL = 'all';
    const GUEST = 'guest';
    const USER = 'user';

    /**
     * Access groups
     *
     * @var array
     */
    protected static $groups = array(
        self::ALL,
        self::GUEST,
        self::USER
    );

    /**
     * Access control list based on groups and routes
     *
     * @var array
     */
    protected $acl;

    /**
     * Constructor
     *
     * @param Space $space Data storage
     */
    public function __construct(Space $space)
    {
        parent::__construct($space);

        $this->acl = array();
    }

    /**
     * Get access groups
     *
     * @return array
     */
    public static function getGroups()
    {
        return self::$groups;
    }

    /**
     * Verify route accessibility using ACL based on user's group
     *
     * @param Route|string $route Route object or name
     *
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function isAccessible($route)
    {
        if (empty($route)) {
            throw new \InvalidArgumentException('ACL auth failed. Invalid route specified.');
        }

        if ($route instanceof Route) {
            $route = $route->getName();
        }

        if ($this->verifyRoute($route, self::ALL)) {
            return true;
        }

        $guestAccess = $this->verifyRoute($route, self::GUEST);

        if (!$this->isAuthorized()) {
            return $guestAccess;
        }

        if (!$guestAccess) {
            if ($this->verifyRoute($route, self::USER)) {
                return true;
            }

            if ($this->verifyCustom($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compile route pattern to regular expression
     *
     * @param string $route Route name with wildcards ('*')
     *
     * @return string
     */
    protected function compileRoute($route)
    {
        $parts = explode(static::WILDCARD, $route);
        foreach ($parts as $p => $part) {
            $parts[$p] = preg_quote($part);
        }
        $regexp = '/^' . implode('(.*)', $parts) . '$/';

        return $regexp;
    }

    /**
     * Verify access to route with extra expressions
     *
     * @param string     $test  Route to be tested
     * @param int|string $group Access group
     *
     * @return boolean
     */
    protected function verifyRoute($test, $group)
    {
        if (empty($this->acl[$group])) {
            return false;
        }

        if (in_array($test, $this->acl[$group])) {
            return true;
        }

        foreach ($this->acl[$group] as $route) {
            $regExp = $this->compileRoute($route);
            if (preg_match($regExp, $test)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify user access associated with external dependencies (group in database etc)
     *
     * @param Route $route Route
     *
     * @return boolean
     */
    protected function verifyCustom($route)
    {
        return false;
    }

    /**
     * Get access control list
     *
     * @return array
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Set access control list
     *
     * @param array $acl Data
     *
     * @return Auth
     */
    public function setAcl(array $acl)
    {
        $this->acl = $acl;

        return $this;
    }
}