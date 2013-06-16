<?

namespace Appcia\Webwork\Data\Form;

use Appcia\Webwork\Core\Component;
use Appcia\Webwork\Data\Filter;
use Appcia\Webwork\Data\Validator;

/**
 * Form field
 *
 * @package Appcia\Webwork\Data\Form
 */
abstract class Field
{
    /**
     * Types
     *
     * Text  - for text inputs with CSRF protection
     * Set   - for servicing set of values (arrays)
     * File  - input type file
     * Plain - plain data (unsafe)
     */
    const TEXT = 'text';
    const SET = 'set';
    const FILE = 'file';
    const PLAIN = 'plain';

    /**
     * Possible types:
     *
     * @var array
     */
    private static $types = array(
        self::TEXT,
        self::SET,
        self::FILE,
        self::PLAIN
    );

    /**
     * Filtered value which is tested by validation
     *
     * @var mixed
     */
    protected $value;

    /**
     * Registered filters
     *
     * @var Filter[]
     */
    protected $filters;

    /**
     * Registered validators
     *
     * @var Validator[]
     */
    protected $validators;

    /**
     * Validation result
     *
     * @var boolean
     */
    protected $valid;

    /**
     * Validation messages
     *
     * @var array
     */
    private $messages;

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * Unfiltered value
     *
     * @var mixed
     */
    private $rawValue;

    /**
     * Additional data useful in views
     * KV storage
     *
     * @var
     */
    private $data;

    /**
     * Constructor
     *
     * @param string $name Name
     */
    public function __construct($name)
    {
        $this->validators = array();
        $this->filters = array();
        $this->valid = true;
        $this->data = array();
        $this->messages = array();

        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name Name
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Field name cannot be empty');
        }

        $this->name = (string) $name;

        return $this;
    }

    /**
     * Get allowed types
     *
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get filtered value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set filtered value
     *
     * @param mixed $value Value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->rawValue = $value;

        return $this;
    }

    /**
     * Check whether value is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->value);
    }

    /**
     * Check how value evaluates to true or false
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->value;
    }

    /**
     * Check if value belongs to set
     * Suppose that value is an array
     *
     * @param mixed $value Value
     *
     * @return boolean
     */
    public function contains($value)
    {
        if (!is_array($this->value)) {
            return false;
        }

        return in_array($value, $this->value);
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Check whether filter is registered
     *
     * @param string $name Filter name
     *
     * @return boolean
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * Get registered filters
     *
     * @return array
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
        $this->filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Register filter
     *
     * @param Filter $filter Filter
     *
     * @return $this
     * @throws \LogicException
     */
    public function addFilter(Filter $filter)
    {
        $name = $filter->getName();

        if (isset($this->filters[$name])) {
            throw new \LogicException(sprintf("Filter '%s' already exist", $name));
        }

        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Check whether validator is registered
     *
     * @param string $name Validator name
     *
     * @return boolean
     */
    public function hasValidator($name)
    {
        return isset($this->validators[$name]);
    }

    /**
     * Get registered validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Set validators
     *
     * @param array $validators
     *
     * @return $this
     */
    public function setValidators($validators)
    {
        $this->validators = array();

        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }

        return $this;
    }

    /**
     * Attach validator to field
     *
     * @param Validator $validator Validator
     *
     * @return $this
     * @throws \LogicException
     */
    public function addValidator(Validator $validator)
    {
        $name = $validator->getName();

        if (isset($this->validators[$name])) {
            throw new \LogicException(sprintf("Field validator '%s' already exist", $name));
        }

        $this->validators[$name] = $validator;

        return $this;
    }

    /**
     * Get all components
     *
     * @return Component[]
     */
    public function getComponents()
    {
        $components = array_merge(
            array_values($this->filters),
            array_values($this->validators)
        );

        return $components;
    }

    /**
     * Get additional data
     *
     * @param string $key Key
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new \OutOfBoundsException(sprintf("Field data '%s' does not exist.", $key));
        }

        return $this->data[$key];
    }

    /**
     * Set additional data
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Apply filters to value
     *
     * @return string
     */
    public function filter()
    {
        foreach ($this->filters as $filter) {
            $this->value = $filter->filter($this->value);
        }

        return $this->value;
    }

    /**
     * @param array $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get validation messages
     *
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Validate value using chain of validators
     *
     * @return boolean
     */
    public function validate()
    {
        $this->valid = true;

        foreach ($this->validators as $validator) {
            if (!$validator->validate($this->value)) {
                $this->valid = false;
                break;
            }
        }

        return $this->valid;
    }

    /**
     * Get as string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}