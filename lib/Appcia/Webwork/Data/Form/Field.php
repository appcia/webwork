<?

namespace Appcia\Webwork\Data\Form;

use Appcia\Webwork\Data\Filter;
use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Exception;

class Field
{
    const TEXT = 'text';
    const FILE = 'file';
    const PLAIN = 'plain';

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * Filtered value which is tested by validation
     *
     * @var mixed
     */
    private $value;

    /**
     * Unfiltered value
     *
     * @var mixed
     */
    private $rawValue;

    /**
     * Registered validators
     *
     * @var array
     */
    private $validators;

    /**
     * Registered filters
     *
     * @var array
     */
    private $filters;

    /**
     * Validation result
     *
     * @var bool
     */
    private $valid;

    /**
     * Field type used for extended form behaviours
     *
     * @var bool
     */
    private $type;

    private static $types = array(
        self::TEXT,
        self::FILE,
        self::PLAIN
    );

    /**
     * Constructor
     *
     * @param string $name Name
     * @param string $type Type
     */
    public function __construct($name, $type = self::TEXT)
    {
        $this->validators = array();
        $this->filters = array();
        $this->valid = true;
        $this->type = self::TEXT;

        $this->setName($name);
        $this->setType($type);
    }

    /**
     * Set name
     *
     * @param string $name Name
     *
     * @return Field
     * @throws Exception
     */
    private function setName($name)
    {
        if (empty($name)) {
            throw new Exception('Field name cannot be empty');
        }

        $this->name = (string) $name;

        return $this;
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
     * Set filtered value
     *
     * @param mixed $value Value
     *
     * @return Field
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->rawValue = $value;

        return $this;
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
     * Check whether value is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->value);
    }

    /**
     * Check how value evaluates to true or false
     *
     * @return bool
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
     * @return bool
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
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param array $filters
     *
     * @return Field
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
     * @return Field
     * @throws Exception
     */
    public function addFilter(Filter $filter)
    {
        $name = $filter->getName();

        if (isset($this->filters[$name])) {
            throw new Exception(sprintf("Filter '%s' already exist"));
        }

        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Check whether filter is registered
     *
     * @param string $name Filter name
     *
     * @return bool
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
     * Set validators
     *
     * @param array $validators
     *
     * @return Field
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
     * @return Field
     * @throws Exception
     */
    public function addValidator(Validator $validator)
    {
        $name = $validator->getName();

        if (isset($this->validators[$name])) {
            throw new Exception(sprintf("Validator '%s' already exist"));
        }

        $this->validators[$name] = $validator;

        return $this;
    }

    /**
     * Check whether validator is registered
     *
     * @param string $name Validator name
     *
     * @return bool
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
     * Get all components
     *
     * @return array
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
     * Validate value using chain of validators
     *
     * @return bool
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
     * Treat value as file that can be uploaded
     *
     * @param string $type Type
     *
     * @return Field
     * @throws Exception
     */
    private function setType($type)
    {
        if (!in_array($type, self::$types)) {
            throw new Exception(sprintf("Invalid field type '%s'", $type));
        }

        if ($type === self::TEXT) {
            $this->addFilter(new Filter\HtmlEntities());
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Get as string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}