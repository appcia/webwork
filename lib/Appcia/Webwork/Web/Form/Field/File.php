<?

namespace Appcia\Webwork\Web\Form\Field;

use Appcia\Webwork\Resource\Resource;
use Appcia\Webwork\Web\Form\Field;
use Appcia\Webwork\Web\Form\Uploader;

/**
 * Field with uploaded resource
 * Remembers that value should be cleared or be saved (file in temporary)
 *
 * @property Uploader form
 */
class File extends Field
{
    /**
     * States
     */
    const UPLOADED = 1;

    const UNLOADED = 2;

    /**
     * @return boolean
     */
    public function isUploaded()
    {
        return $this->form->getMetadata($this->name) == static::UPLOADED;
    }

    /**
     * @return boolean
     */
    public function isUnloaded()
    {
        return $this->form->getMetadata($this->name) == static::UNLOADED;
    }

    /**
     * @param $data
     *
     * @throws \LogicException
     * @return $this
     */
    public function upload($data)
    {
        $token = $this->form->getMetadata(Uploader::CSRF);
        if ($token === null) {
            throw new \LogicException('Form CSRF protection must be enabled to use file fields.');
        }

        $params = array(
            'token' => $token,
            'key' => $this->name
        );

        $manager = $this->form->getManager();

        $data = $manager->normalizeUpload($data);
        $resource = $manager->upload($data, $params);

        if ($resource !== null && $resource->getFile()->exists()) {
            $this->value = $resource;
            $this->form->setMetadata($this->name, static::UPLOADED);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unload()
    {
        if ($this->value instanceof Resource) {
            $this->value->remove();
        }

        $this->value = null;
        $this->form->setMetadata($this->name, static::UNLOADED);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->isUnloaded()) {
            return null;
        }

        return parent::getValue();
    }
}