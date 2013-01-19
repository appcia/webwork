<?

namespace Appcia\Webwork\Data;

use Appcia\Webwork\Data\Form\Field;

class Form
{
    /**
     * @var bool
     */
    private $valid;

    /**
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

        $this->build();
    }

    /**
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
     * @param Form\Field $field
     *
     * @return Form
     * @throws \InvalidArgumentException
     */
    public function addField(Field $field)
    {
        $name = $field->getName();

        if (isset($this->fields[$name])) {
            throw new \InvalidArgumentException(sprintf("Field '%s' already exist", $name));
        }

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get field value
     *
     * @param string $name Field name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        if (!isset($this->fields[$name])) {
            throw new \InvalidArgumentException(sprintf("Field '%s' does not exist", $name));
        }

        $field = $this->fields[$name];

        return $field->getValue();
    }

    /**
     * Get all field values
     *
     * @return array
     */
    public function getAll() {
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
     * @throws \InvalidArgumentException
     */
    public function set($name, $value)
    {
        if (!isset($this->fields[$name])) {
            throw new \InvalidArgumentException(sprintf("Field '%s' does not exist", $name));
        }

        $field = $this->fields[$name];
        $field->setValue($value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
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
     * @param array $data     Input data
     * @param bool  $unknowns Accept unknown values
     *
     * When unknowns flag is true, then fields are created automatically
     * Safer when false, prevent for XSRF attacks
     *
     * @return Form
     */
    public function populate(array $data, $unknowns = true)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $field = $this->fields[$name];
                $field->setValue($value);
            }
            else if ($unknowns) {
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
     * @param $obj
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
    }

    /**
     * Get field value using $form->{fieldName}() in templates
     *
     * @param string $name Field name
     * @param array  $args Not used
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($name, $args) {
        if (!empty($args)) {
            throw new\InvalidArgumentException('Arguments are not allowed, when accessing form fields');
        }

        if (isset($this->fields[$name])) {
            $field = $this->fields[$name];

            return $field->getValue();
        }
    }
}