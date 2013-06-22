<?

namespace Appcia\Webwork\Storage\Config\Writer;

use Appcia\Webwork\Storage\Config\Writer;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\System\File;

class Php extends Writer
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $author;

    /**
     * @var bool|string
     */
    private $since;

    /**
     * @var string
     */
    private $template;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->title = 'Configuration';
        $this->since = date('y-m-d H:i:s');
        $this->author = get_current_user();
        $this->template = file_get_contents(dirname(__FILE__) . '/Php/Template.tpl');
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $since
     *
     * @return $this
     */
    public function setSince($since)
    {
        $this->since = (string) $since;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Config $config, $target)
    {
        $file = new File($target);

        if ($file->exists()) {
            throw new \LogicException(sprintf("Config target file already exists '%s'", $target));
        }

        if (empty($this->template)) {
            throw new \LogicException("Writer template is not specified.");
        }

        // Dates, float numbers should be stored in default PHP format
        $locale = setlocale(LC_ALL, 'en_US.UTF-8');

        $config = $config->flatten();
        $values = array();

        foreach ($config as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $key = explode('.', $key);
            $key = implode("']['", $key);

            if (is_string($value)) {
                $value = sprintf("'%s'", $value);
            }

            $node = sprintf("\$config['%s'] = %s;", $key, $value);
            $values[] = $node;
        }

        $data = implode(PHP_EOL, $values);

        $result = str_replace(
            array('{title}', '{author}', '{since}', '{data}'),
            array($this->title, $this->author, $this->since, $data),
            $this->template
        );

        // Recover default format
        setlocale(LC_ALL, $locale);

        $file->write($result);
    }
}