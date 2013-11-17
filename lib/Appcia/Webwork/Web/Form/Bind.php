<?

namespace Appcia\Webwork\Web\Form;

use Appcia\Webwork\Data\Arr;
use Appcia\Webwork\Data\Component\Filter;
use Appcia\Webwork\Data\Component;
use Appcia\Webwork\Data\Component\Validator;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Form;

/**
 * Field binding
 */
class Bind implements \IteratorAggregate, \Countable
{
    /**
     * Field value binding
     */
    const VALUE = 'bind';

    /**
     * Unique name
     *
     * @var string
     */
    protected $name;

    /**
     * Binded values
     *
     * @var array
     */
    protected $values;

    /**
     * Field creator
     * Can be used for multiple fields per bind value (just return associative array)
     *
     * @var callable
     */
    protected $creator;

    /**
     * Binded value updater
     *
     * @var callable
     */
    protected $updater;

    /**
     * Fields related to binded value
     *
     * @var array
     */
    protected $fields;

    /**
     * Constructor
     *
     * @param Form     $form    Base form
     * @param string   $name    Unique name
     * @param array    $values  Values to be binded
     * @param callable $creator Fields creator
     * @param callable $updater Binded values updater
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Form $form, $name, $values, $creator, $updater)
    {
        $this->form = $form;
        $this->name = $name;
        $this->values = Arr::traversable($values) ? $values : (array) $values;

        if (!is_callable($creator)) {
            throw new \InvalidArgumentException(sprintf("Form bind '%s' creator is not callable.", $this->name));
        }
        $this->creator = $creator;

        if (!is_callable($creator)) {
            throw new \InvalidArgumentException(sprintf("Form bind '%s' updater is not callable.", $this->name));
        }
        $this->updater = $updater;

        $form->addBind($this);
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * Create fields using callback basing on one of values from set
     *
     * @return $this
     */
    public function create()
    {
        $creator = $this->creator;
        foreach ($this->values as $index => $value) {
            $fields = $creator($this, $value, $index);
            $this->bind($value, $fields);

            $this->fields[$index] = $fields;
        }

        return $this;
    }

    /**
     * Test and bind value to all of fields produced by creator
     *
     * @param $value
     * @param $fields
     *
     * @return $this
     * @throws \UnexpectedValueException
     */
    protected function bind($value, $fields)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                throw new \UnexpectedValueException(sprintf(
                    "Form bind '%s' creator must always form fields.",
                    $this->name
                ));
            }

            $field->setBind(static::VALUE, $value);
        }

        return $this;
    }

    /**
     * Update binded values basing on field values
     *
     * @return $this
     */
    public function update()
    {
        $updater = $this->updater;
        foreach ($this->values as $index => $value) {
            $this->values[$index] = $updater($this, $value, $this->fields[$index]);
        }

        return $this;
    }
}