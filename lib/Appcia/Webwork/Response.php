<?

namespace Appcia\Webwork;

class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $protocol;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = 0;
    }

    /**
     * Set response content
     *
     * @param $content
     *
     * @return Response
     */
    public function setContent($content)
    {
        $this->content = (string)$content;

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
     * Set response status
     *
     * @param int $status Status code
     *
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = (int)$status;

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
     * @return Response
     */
    public function setProtocol($protocol)
    {
        $this->protocol = (string)$protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Send headers
     *
     * @return void
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
    }

    /**
     * Display output in browser
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

