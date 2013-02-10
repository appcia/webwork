<?

namespace Appcia\Webwork\Data\Form;

use Appcia\Webwork\Data\Validator;
use Appcia\Webwork\Data\Filter;

class Field {

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $rawValue;

    /**
     * @var bool
     */
    private $valid;

    /**
     * @var array
     */
    private $validators;

    /**
     * @var array
     */
    private $filters;

    /**
     * Constructor
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __construct($name, $value = null) {
        $this->validators = array();
        $this->filters = array();
        $this->valid = true;

        $this->setName($name);

        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->rawValue = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
    public function isValid() {
        return $this->valid;
    }

    /**
     * @param array $filters
     */
    public function setFilters($filters)
    {
        $this->filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * Attach filter to field
     *
     * @param Filter $filter
     * @throws \InvalidArgumentException
     */
    public function addFilter(Filter $filter) {
        $name = $filter->getName();

        if (isset($this->filters[$name])) {
            throw new \InvalidArgumentException(sprintf("Filter '%s' already exist"));
        }

        $this->filters[$name] = $filter;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $validators
     */
    public function setValidators($validators)
    {
        $this->validators = array();

        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
    }

    /**
     * Attach validator to field
     *
     * @param Validator $validator
     * @throws \InvalidArgumentException
     */
    public function addValidator(Validator $validator) {
        $name = $validator->getName();

        if (isset($this->validators[$name])) {
            throw new \InvalidArgumentException(sprintf("Validator '%s' already exist"));
        }

        $this->validators[$name] = $validator;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Apply filters to value
     *
     * @return string
     */
    public function filter() {
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
    public function validate() {
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
    public function __toString() {
        return (string) $this->value;
    }
}