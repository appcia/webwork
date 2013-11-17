<?

namespace Appcia\Webwork\Web\Form;

use Appcia\Webwork\Data\Component;
use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Web\Form;
use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Data\Value;
use Appcia\Webwork\Storage\Config;

/**
 * Form field
 */
abstract class Field
{
    /**
     * Form
     *
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $name;

    /**
     * Filtered value which is tested by validation
     *
     * @var mixed
     */
    protected $value;

    /**
     * Unfiltered value
     *
     * @var mixed
     */
    protected $rawValue;

    /**
     * Registered validators
     *
     * @var Validator[]
     */
    protected $validators;

    /**
     * Registered filters
     *
     * @var Filter[]
     */
    protected $filters;

    /**
     * Validation result
     *
     * @var boolean
     */
    protected $valid;

    /**
     * Additional data useful in views
     * KV storage
     *
     * @var
     */
    protected $binds;

    /**
     * Constructor
     *
     * @param Form   $form  Form
     * @param string $name  Name
     * @param mixed  $value Initial value
     */
    public function __construct(Form $form, $name, $value = null)
    {
        $this->form = $form;
        $this->validators = array();
        $this->filters = array();
        $this->valid = true;
        $this->binds = array();

        $this->setName($name);
        $this->setValue($value);

        $form->addField($this);
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set name
     *
     * @param string $name Name
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function setName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Field name cannot be empty');
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
     * Get all components (filters and validators)
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
     * Get registered filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param Filter[] $filters
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
        $name = get_class($filter);

        if (isset($this->filters[$name])) {
            throw new \LogicException(sprintf("Filter '%s' already exist", $name));
        }

        $this->filters[$name] = $filter;

        return $this;
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
     * @param Validator[] $validators
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
     *
     * @return $this
     * @throws \LogicException
     */
    public function addValidator(Validator $validator)
    {
        $name = get_class($validator);

        if (isset($this->validators[$name])) {
            throw new \LogicException(sprintf("Field validator '%s' already exist", $name));
        }

        $this->validators[$name] = $validator;

        return $this;
    }

    /**
     * Get field related object (bind) by name
     *
     * @param string $name Key
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function getBind($name)
    {
        if (!array_key_exists($name, $this->binds)) {
            throw new \OutOfBoundsException(sprintf("Field bind '%s' does not exist.", $name));
        }

        return $this->binds[$name];
    }

    /**
     * @see getBind()
     */
    public function __get($name)
    {
        return $this->getBind($name);
    }

    /**
     * Bind related object to field
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return $this
     */
    public function setBind($key, $value)
    {
        $this->binds[$key] = $value;

        return $this;
    }

    /**
     * @see setBind()
     */
    public function __set($key, $value)
    {
        return $this->setBind($key, $value);
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
        return (string) Value::getString($this->value);
    }

    /**
     * Check whether value is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return Value::isEmpty($this->value);
    }

    /**
     * Check value evaluated to boolean
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->value;
    }

    /**
     * Check if value belongs to set
     *
     * @param mixed   $value Value
     * @param boolean $keys  Check value key and corresponding value
     *
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function isContained($value, $keys = true)
    {
        $flag = null;
        if ($this->value instanceof \ArrayAccess) {
            $flag = $keys
                && isset($this->value[$value])
                && $this->value[$value];
        } elseif (is_array($this->value)) {
            $flag = in_array($value, $this->value)
                || ($keys && (array_key_exists($value, $this->value) && $this->value[$value]));
        } else {
            throw new \InvalidArgumentException(sprintf(
                "Field '%s' value is not a set of values so containment cannot be checked.",
                $this->name
            ));
        }

        return $flag;
    }

    /**
     * Check whether component (filter or validator) is used
     *
     * @param string $component Component name (last part of class name)
     * @param array  $classes   Component classes to be searched
     *
     * @return bool
     */
    protected function hasComponent($component, array $classes)
    {
        $names = array_map(function ($class) {
            return mb_strtolower(substr($class, strrpos($class, '\\') + 1));
        }, $classes);
        $flag = in_array(mb_strtolower($component), $names);

        return $flag;
    }

    /**
     * Check whether validator with specified name is used
     *
     * @param string $name Validator name
     *
     * @return bool
     */
    public function isValidated($name = null)
    {
        if ($name === null) {
            return !empty($this->validators);
        }
        $flag = $this->hasComponent($name, array_keys($this->validators));

        return $flag;
    }

    /**
     * Check whether filter with specified name is used
     *
     * @param string $name Validator name
     *
     * @return bool
     */
    public function isFiltered($name = null)
    {
        if ($name === null) {
            return !empty($this->filters);
        }
        $flag = $this->hasComponent($name, array_keys($this->filters));

        return $flag;
    }
}