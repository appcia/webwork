<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Data\Form\Field;
use Appcia\Webwork\Exception;

class Form
{
    const TOKEN_SALT = 'dskljakld32#%$@#343_';
    const METADATA = 'metadata';

    /**
     * Validation result
     *
     * @var bool
     */
    private $valid;

    /**
     * Fields
     *
     * @var array
     */
    private $fields;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = array();
        $this->valid = true;

        $this->addField(new Field(self::METADATA));
        $this->build();
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
     * @param Field $field
     *
     * @return Form
     * @throws Exception
     */
    public function addField(Field $field)
    {
        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new Exception(sprintf("Field '%s' already exist", $name));
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
     * @throws Exception
     */
    public function getField($name)
    {
        if (!isset($this->fields[$name])) {
            throw new Exception(sprintf("Field '%s' does not exist", $name));
        }

        return $this->fields[$name];
    }

    /**
     * Get all fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set metadata
     *
     * @param array $metadata Data
     *
     * @return $this
     */
    public function setMetadata(array $metadata)
    {
        $serializer = new Serializer();
        $value = $serializer->serialize($metadata);

        $this->set(self::METADATA, $value);

        return $this;
    }

    /**
     * Get metadata
     *
     * @return array
     * @throws Exception
     */
    public function getMetadata()
    {
        $value = $this->get(self::METADATA);

        $serializer = new Serializer();
        $metadata = $serializer->unserialize($value);

        return $metadata;
    }

    /**
     * Get field value
     *
     * @param string $name Field name
     *
     * @return mixed
     * @throws Exception
     */
    public function get($name)
    {
        if (!isset($this->fields[$name])) {
            throw new Exception(sprintf("Field '%s' does not exist", $name));
        }

        $field = $this->fields[$name];

        return $field->getValue();
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
    public function getAll()
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
     * @throws Exception
     */
    public function set($name, $value)
    {
        if (!isset($this->fields[$name])) {
            throw new Exception(sprintf("Field '%s' does not exist", $name));
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
     * Build a form fields
     *
     * Useful when inherited, invoked by constructor
     *
     * @return Form
     */
    public function build()
    {
        return $this;
    }

    /**
     * Populate form by data
     *
     * When unknowns flag is true, then fields are created automatically
     * Safer when false, prevent for XSRF attacks
     *
     * @param array $data     Input data
     * @param bool  $unknowns Accept unknown values
     *
     * @return Form
     */
    public function populate(array $data, $unknowns = true)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $field = $this->fields[$name];
                $field->setValue($value);
            } else if ($unknowns) {
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
     * @param Object $obj Target object
     *
     * @return Form
     */
    public function inject($obj)
    {
        foreach ($this->getAll() as $prop => $value) {
            if ($value === null) {
                continue;
            }

            $callback = array($obj, 'set' . ucfirst($prop));

            if (is_callable($callback)) {
                call_user_func($callback, $value);
            }
        }

        return $this;
    }

    /**
     * Suck values from object using getters
     *
     * @param object $obj Source object
     *
     * @return Form
     */
    public function suck($obj)
    {
        foreach ($this->fields as $prop => $field) {
            $callback = array($obj, 'get' . ucfirst($prop));

            if (is_callable($callback)) {
                $value = call_user_func($callback);

                if ($value !== null) {
                    $field->setValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * Generate token basing on field names and custom key
     *
     * @param string|null $key Custom key, could be some date if token should expire after some time
     *
     * @return string
     * @throws Exception
     */
    public function tokenize($key = null)
    {
        if ($key !== null && !is_string($key) && !is_numeric($key)) {
            throw new Exception('Token key should be a number or a string');
        }

        $key = (string) $key . implode('', array_keys($this->fields));
        $token = sha1(md5($key . self::TOKEN_SALT));

        return $token;
    }

    /**
     * Magic getter for $form->{fieldName} in templates
     *
     * @param string $name Field name
     *
     * @return Field
     */
    public function __get($name)
    {
        return $this->getField($name);
    }

}