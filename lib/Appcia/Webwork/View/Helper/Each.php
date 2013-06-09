<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Each extends Helper
{
    /**
     * Caller
     *
     * @param \Traversable|array $values   Values to be iterated
     * @param callable           $callback Callback
     *
     * @return array
     */
    public function each($values, \Closure $callback)
    {
        if (!$this->isTraversableValue($values) || !is_callable($callback)) {
            return array();
        }

        $renderer = $this->getView()
            ->getRenderer();

        $results = array();

        foreach ($values as $value) {
            $args = array($renderer, $value);
            $result = call_user_func_array($callback, $args);

            $results[] = $result;
        }

        return $results;
    }
}
