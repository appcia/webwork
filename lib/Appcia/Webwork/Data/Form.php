<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Core\Component;
use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Model\Template;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Context;

/**
 * General utility for servicing web forms (data manipulation)
 *
 * @package Appcia\Webwork\Resource
 */
class Form extends Component
{
    const METADATA = 'metadata';

    /**
     * Use context
     *
     * @var Context
     */
    protected $context;

    /**
     * Data encoder
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     * @var Encrypter
     */
    protected $encryter;

    /**
     * Fields
     *
     * @var Field[]
     */
    protected $fields;

    /**
     * Metadata field
     *
     * @var Field
     */
    protected $metadata;

    /**
     * Validation result
     *
     * @var boolean
     */
    protected $valid;

    /**
     * Constructor
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->fields = array();
        $this->valid = true;
        $this->encoder = new Encoder();
        $this->encryter = new Encrypter();
        $this->metadata = new Field\Plain(self::METADATA, $this->encoder->encode(array()));

        $this->build();
        $this->prepare();
    }

    /**
     * Build a form fields
     *
     * Useful when inherited, invoked by constructor
     *
     * @return $this
     */
    protected function build()
    {
        return $this;
    }

    /**
     * Prepare built field
     * Propagate use context for components
     *
     * @return $this
     */
    protected function prepare()
    {
        foreach ($this->fields as $field) {
            foreach ($field->getComponents() as $component) {
                $component->setContext($this->context);
            }

            $field->prepare();
        }

        return $this;
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
     * Set fields
     *
     * @param array $fields
     *
     * @return $this
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
     * @return $this
     * @throws \LogicException
     */
    public function addField(Field $field)
    {
        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new \LogicException(sprintf("Field '%s' already exist.", $name));
        }

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Check whether field already exists
     *
     * @param string $name Field name
     *
     * @return boolean
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * Group fields by specified pattern
     * Creates multi-dimensional tree in which each level corresponds to pattern parameter
     * Leafs are form fields
     *
     * @param string $template Field name template
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function groupFields($template)
    {
        $template = new Template($template);
        $result = array();

        foreach ($this->fields as $name => $field) {
            $match = array();

            if (preg_match($template->getRegExp(), $name, $match)) {
                unset($match[0]);
                $match[] = $field;

                $fields = $this->nestArray($match);
                $result = array_merge_recursive($result, $fields);
            }
        }

        return $result;
    }

    /**
     * Create nested array from flat
     * Creates a branch, merging with another produces tree
     *
     * @param array $arr Flat 1D array
     *
     * @return array
     */
    protected function nestArray($arr)
    {
        $res = array();
        $curr = & $res;

        foreach ($arr as $val) {
            if ($val === end($arr)) {
                $curr = $val;
            } else {
                $curr[$val] = array();
                $curr = & $curr[$val];
            }
        }

        return $res;
    }

    /**
     * Get metadata
     *
     * @param string $key Data key
     *
     * @return mixed
     */
    public function getMetadata($key)
    {
        $data = $this->metadata->getValue();
        $metadata = $this->encoder->decode($data);

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Set metadata
     *
     * @param mixed $key   Data key
     * @param mixed $value Data value
     *
     * @return $this
     */
    public function setMetadata($key, $value)
    {
        $data = $this->metadata->getValue();
        $metadata = $this->encoder->decode($data);

        $metadata[$key] = $value;

        $data = $this->encoder->encode($metadata);
        $this->metadata->setValue($data);

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
     * Set data encoder
     *
     * @param Encoder $encoder Encoder
     *
     * @return $this
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Set token encrypter
     *
     * @param Encrypter $encryter
     *
     * @return $this
     */
    public function setEncryter($encryter)
    {
        $this->encryter = $encryter;

        return $this;
    }

    /**
     * Get token encrypter
     *
     * @return Encrypter
     */
    public function getEncryter()
    {
        return $this->encryter;
    }

    /**
     * Is field contain not empty value
     *
     * @param string $name Field name
     *
     * @return boolean
     */
    public function has($name)
    {
        $value = $this->get($name);
        $has = !$this->isEmptyValue($value);

        return $has;
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
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist.", $name));
        }

        $field = $this->fields[$name];
        $value = $this->getStringValue($field->getValue());

        return $value;
    }

    /**
     * Set field value
     *
     * @param string $name  Field name
     * @param mixed  $value Field value
     *
     * @return $this
     * @throws \OutOfBoundsException
     */
    public function set($name, $value)
    {
        if (!isset($this->fields[$name])) {
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist.", $name));
        }

        $field = $this->fields[$name];
        $field->setValue($value);

        return $this;
    }

    /**
     * @return boolean
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
     * @return $this
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

        $this->service();

        return $this;
    }

    /**
     * Service populated data quietly
     *
     * @return $this
     */
    protected function service()
    {
        return $this;
    }

    /**
     * Create fields using initial data
     *
     * @param array $data Data
     *
     * @return $this
     * @throws \LogicException
     */
    public function init(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                throw new \LogicException(sprintf("Field '%s' already exists and cannot be initialized.", $name));
            } else {
                $field = new Field\Text($name);
                $field->setValue($value);

                $this->fields[$name] = $field;
            }
        }

        return $this;
    }

    /**
     * Validate field values
     *
     * @return boolean
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
     * @return boolean
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
     * @param Object  $object    Target object
     * @param boolean $populated Only populated values (skip nulls)
     *
     * @return $this
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
     * Get all field values
     *
     * @return array
     */
    public function getData()
    {
        $values = array();

        foreach ($this->fields as $field) {
            $name = $field->getName();
            $values[$name] = $field->getValue();
        }

        return $values;
    }

    /**
     * Suck values from object using getters or direct from array
     *
     * @param object|array $source  Source object or array
     * @param boolean      $defined Only defined values (skip nulls)
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function suck($source, $defined = true)
    {
        foreach ($this->fields as $property => $field) {
            if (is_object($source)) {
                foreach (array('get', 'is') as $prefix) {
                    $method = $prefix . ucfirst($property);
                    $callback = array($source, $method);

                    if (method_exists($source, $method) && is_callable($callback)) {
                        $value = call_user_func($callback);

                        if ($value !== null || !$defined) {
                            $field->setValue($value);
                        }

                        break;
                    }
                }
            } elseif (is_array($source)) {
                if (array_key_exists($property, $source)) {
                    $value = $source[$property];

                    if ($value !== null || !$defined) {
                        $field->setValue($value);
                    }
                }
            } else {
                throw new \InvalidArgumentException("Form sucking expected object or array as source.");
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
            throw new \InvalidArgumentException('Form token key should be a number or a string.');
        }

        $value = implode('', array_keys($this->fields));
        $token = $this->encryter->setSalt($salt)
            ->crypt($value);

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
        $field = $this->getField($name);

        return $field;
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
        $field = null;

        if ($name === self::METADATA) {
            $field = $this->metadata;
        } elseif (isset($this->fields[$name])) {
            $field = $this->fields[$name];
        } else {
            throw new \OutOfBoundsException(sprintf("Field '%s' does not exist.", $name));
        }

        return $field;
    }

}