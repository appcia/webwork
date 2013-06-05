<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Data\Form\Field;

/**
 * General utility for servicing web forms (data manipulation)
 *
 * @package Appcia\Webwork\Resource
 */
class Form
{
    const METADATA = 'metadata';

    /**
     * Use context
     *
     * @var Context
     */
    private $context;

    /**
     * Data encoder
     *
     * @var Encoder
     */
    private $encoder;

    /**
     * Fields
     *
     * @var array
     */
    private $fields;

    /**
     * Metadata field
     *
     * @var Field
     */
    private $metadata;

    /**
     * Validation result
     *
     * @var bool
     */
    private $valid;

    /**
     * Constructor
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->fields = array();
        $this->valid = true;
        $this->encoder = new Encoder(Encoder::BASE64);
        $this->metadata = new Field(self::METADATA);

        $this->build();
        $this->prepare();
    }

    /**
     * Get use context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set fields
     *
     * @param array $fields
     *
     * @return Form
     */
    public function setFields($fields)
    {
        $this->fields = array();
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * Add a field
     *
     * @param Field $field Field
     *
     * @return Form
     * @throws \LogicException
     */
    public function addField(Field $field)
    {
        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new \LogicException(sprintf("Field '%s' already exist", $name));
        }

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Check whether field already exists
     *
     * @param string $name Field name
     *
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * Get field by name
     *
     * @param string $name Field name
     *
     * @return Field
     * @throws \OutOfBoundsException
     */
    public function getField($name)
    {
        if (!isset($this->fields[$name])) {
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist", $name));
        }

        return $this->fields[$name];
    }

    /**
     * Get all standard fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = $this->fields;

        return $fields;
    }

    /**
     * Get fields by filtering by name
     *
     * @param string $pattern Regular expression
     *
     * @return array
     */
    public function filterFields($pattern)
    {
        $fields = array();

        $match = array();
        foreach ($this->fields as $name => $field) {
            if (preg_match($pattern, $name, $match)) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Set metadata
     *
     * @param mixed $metadata Data
     *
     * @return Form
     */
    public function setMetadata(array $metadata)
    {
        $value = $this->encoder->encode($metadata);
        $this->metadata->setValue($value);

        return $this;
    }

    /**
     * Get metadata
     *
     * @return mixed
     */
    public function getMetadata()
    {
        $value = $this->metadata->getValue();
        $metadata = $this->encoder->decode($value);

        return $metadata;
    }

    /**
     * Set data encoder
     *
     * @param Encoder $encoder Encoder
     *
     * @return Form
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Get data encoder
     *
     * @return Encoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Get field value
     *
     * @param string $name Field name
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($name)
    {
        if (!isset($this->fields[$name])) {
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist", $name));
        }

        $field = $this->fields[$name];
        $value = $field->getValue();

        return $value;
    }

    /**
     * Is field contain not empty value
     *
     * @param string $name Field name
     *
     * @return bool
     */
    public function has($name)
    {
        $value = $this->get($name);

        return !empty($value);
    }

    /**
     * Get all field values
     *
     * @return array
     */
    public function getData()
    {
        $values = array();

        foreach ($this->fields as $field) {
            $values[$field->getName()] = $field->getValue();
        }

        return $values;
    }

    /**
     * Set field value
     *
     * @param string $name  Field name
     * @param mixed  $value Field value
     *
     * @return Form
     * @throws \OutOfBoundsException
     */
    public function set($name, $value)
    {
        if (!isset($this->fields[$name])) {
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist", $name));
        }

        $field = $this->fields[$name];
        $field->setValue($value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Populate form by data
     *
     * @param array $data Data
     *
     * @return Form
     */
    public function populate(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $field = $this->fields[$name];
                $field->setValue($value);
            }
        }

        if (isset($data[self::METADATA])) {
            $this->metadata->setValue($data[self::METADATA]);
        }

        return $this;
    }

    /**
     * Create fields using initial data
     *
     * @param array $data Data
     *
     * @return Form
     * @throws \LogicException
     */
    public function init(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                throw new \LogicException(sprintf("Field '%s' already exists and cannot be initialized.", $name));
            } else {
                $field = new Field($name);
                $field->setValue($value);

                $this->fields[$name] = $field;
            }
        }

        return $this;
    }

    /**
     * Validate field values
     *
     * @return bool
     */
    public function validate()
    {
        $this->valid = true;

        foreach ($this->fields as $field) {
            if (!$field->validate()) {
                $this->valid = false;
                break;
            }
        }

        return $this->valid;
    }

    /**
     * Filter field values
     */
    public function filter()
    {
        foreach ($this->fields as $field) {
            $field->filter();
        }
    }

    /**
     * Filter and validate form at the same time
     *
     * @return bool
     */
    public function process()
    {
        $this->valid = true;

        foreach ($this->fields as $field) {
            $field->filter();

            if (!$field->validate()) {
                $this->valid = false;
            }
        }

        return $this->valid;
    }

    /**
     * Inject values by object setters
     *
     * @param Object $object    Target object
     * @param bool   $populated Only populated values (skip nulls)
     *
     * @return Form
     */
    public function inject($object, $populated = true)
    {
        $data = $this->getData();

        foreach ($data as $property => $value) {
            if ($populated && $value === null) {
                continue;
            }

            $method = 'set' . ucfirst($property);
            $callback = array($object, $method);

            if (method_exists($object, $method) && is_callable($callback)) {
                call_user_func($callback, $value);
            }
        }

        return $this;
    }

    /**
     * Suck values from object using getters
     *
     * @param object $object  Source object
     * @param bool   $defined Only defined values (skip nulls)
     *
     * @return Form
     */
    public function suck($object, $defined = true)
    {
        foreach ($this->fields as $property => $field) {
            foreach (array('get', 'is') as $prefix) {
                $method = $prefix . ucfirst($property);
                $callback = array($object, $method);

                if (method_exists($object, $method) && is_callable($callback)) {
                    $value = call_user_func($callback);

                    if ($value !== null || !$defined) {
                        $field->setValue($value);
                    }

                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Generate token basing on field names and custom key
     *
     * @param string|null $salt Salt
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function tokenize($salt = null)
    {
        if ($salt !== null && !is_string($salt) && !is_numeric($salt)) {
            throw new \InvalidArgumentException('Form token key should be a number or a string');
        }

        $salt = (string) $salt . implode('', array_keys($this->fields));
        $token = sha1(md5($salt));

        return $token;
    }

    /**
     * Magic getter for $form->{fieldName}
     * Useful in view templates
     *
     * @param string $name Field name
     *
     * @return Field
     */
    public function __get($name)
    {
        $field = null;
        if ($name === self::METADATA) {
            $field = $this->metadata;
        } else {
            $field = $this->getField($name);
        }

        return $field;
    }

    /**
     * Build a form fields
     *
     * Useful when inherited, invoked by constructor
     *
     * @return Form
     */
    protected function build()
    {
        return $this;
    }

    /**
     * Prepare built field
     * Propagate use context for components
     *
     * @return Form
     */
    protected function prepare()
    {
        foreach ($this->fields as $field) {
            foreach ($field->getComponents() as $component) {
                $component->setContext($this->context);
            }
        }

        return $this;
    }

    /**
     * Create multiple fields at once
     *
     * Closure is used for single field configuration
     * Pattern and mappings are used for name generation
     *
     * @param string   $pattern  Field name pattern
     * @param array    $mappings Parameters to be used in pattern
     * @param callable $closure  Single field configurator
     *
     * @return $this
     * @throws \ErrorException
     */
    public function map($pattern, array $mappings, \Closure $closure)
    {
        foreach ($this->permuteMappings($mappings) as $mapping) {
            $maps = array_keys($mapping);
            $properties = array_values($mapping);

            foreach ($maps as $key => $value) {
                $maps[$key] = '{' . $value . '}';
            }

            $name = str_replace($maps, $properties, $pattern);

            $field = new Field($name);

            if (!is_callable($closure)) {
                throw new \ErrorException("Form field mapping callback is not callable.");
            }

            $params = array($field, $mapping);
            call_user_func_array($closure, $params);

            $this->addField($field);

        }

        return $this;
    }

    /**
     * Generate all possible fields without losing parameters order
     *
     * @param array $data Mapped parameters
     * @param bool  $flag Recursion guard
     *
     * @return array|mixed
     * @throws \InvalidArgumentException
     */
    private function permuteMappings(array $data, $flag = false)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Form field mappings requires at least one array.');
        }

        if (count($data) == 1) {
            return array_pop($data);
        }

        $keys = array_keys($data);

        $a = array_shift($data);
        $k = array_shift($keys);

        $b = $this->permuteMappings($data, true);

        $return = array();

        foreach ($a as $v) {
            if ($v) {
                foreach ($b as $v2) {
                    if ($flag == true) {
                        $return[] = array_merge(array($v), (array) $v2);
                    } else {
                        $return[] = array($k => $v) + array_combine($keys, (array) $v2);
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Service values from many fields (to create many entities at once)
     *
     * @param string       $pattern  Field name pattern
     * @param array|string $groupBy  Group mapped parameters (except one)
     * @param array        $mappings Parameters mapped for field name generation
     * @param callable     $closure  Callback used for servicing values from matched fields
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function remap($pattern, $groupBy, array $mappings, \Closure $closure)
    {

        $data = array();
        foreach ($this->permuteMappings($mappings) as $mapping) {
            $maps = array_keys($mapping);
            $properties = array_values($mapping);

            foreach ($maps as $key => $value) {
                $maps[$key] = '{' . $value . '}';
            }

            $name = str_replace($maps, $properties, $pattern);
            $field = $this->getField($name);
            $value = $field->getValue();

            $grouping = $mapping[$groupBy];
            unset($mapping[$groupBy]);

            $param = array_pop($mapping);

            if (!empty($mapping)) {
                throw new \InvalidArgumentException(
                    "Form field remapping expects that grouping covers all mappings except one."
                );
            }

            $data[$grouping][0] = $grouping;
            $data[$grouping][1][$param] = $value;
        }

        $results = array();
        foreach ($data as $args) {
            $result = call_user_func_array($closure, $args);
            $results[] = $result;
        }

        return $results;
    }
}