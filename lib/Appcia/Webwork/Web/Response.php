<?

namespace Appcia\Webwork\Web;

/**
 * Web response representation
 *
 * @package Appcia\Webwork\Web
 */
class Response
{
    /**
     * Content
     *
     * @var string
     */
    protected $content;

    /**
     * Status code
     *
     * @var int
     */
    protected $status;

    /**
     * Protocol
     *
     * @var string
     */
    protected $protocol;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = 0;
    }

    /**
     * Set response content
     * Do not use when generating content using view (default behaviour)
     *
     * @param mixed $content Content
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setContent($content)
    {
        if ($content !== null && !is_scalar($content)) {
            throw new \InvalidArgumentException('Response content should be a text or even scalar value');
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Get actually generated content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Check whether content is set
     *
     * @return boolean
     */
    public function hasContent()
    {
        return $this->content !== null;
    }

    /**
     * Set response status
     *
     * @param int $status Status code
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;

        return $this;
    }

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set protocol (http, https)
     *
     * @param string $protocol
     *
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = (string) $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Send headers
     *
     * @return $this
     */
    public function sendHeaders()
    {
        switch ($this->getStatus()) {
            case 200:
                header($this->getProtocol() . " 200 OK", true, 200);
                break;
            case 404:
                header($this->getProtocol() . " 404 Not Found", true, 404);
                break;
            case 500:
                header($this->getProtocol() . ' 500 Internal Server Error', true, 500);
                break;
        }

        return $this;
    }

    /**
     * Clean current output
     *
     * @return $this
     */
    public function clean()
    {
        ob_clean();

        return $this;
    }

    /**
     * Write to output
     *
     * @return void
     */
    public function display()
    {
        $this->sendHeaders();
        echo $this->getContent();
    }

    /**
     * Break reponse, redirect to another url
     *
     * @param $url
     */
    public function redirect($url)
    {
        if (empty($url)) {
            $url = '/';
        }

        header(sprintf("Location: %s", $url));
        exit(0);
    }
}

