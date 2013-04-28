<?

namespace App\Resource\Processor;

use Appcia\Webwork\Exception;
use Appcia\Webwork\Resource\Processor;
use Appcia\Webwork\Resource;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\System\File;
use WideImage;

class Thumbnail extends Processor
{

    /**
     * {@inheritdoc}
     */
    public function process(Resource $source, array $settings)
    {
        $s = $this->parseSettings($settings);

        $extension = $source->getFile()
            ->getExtension();
        $sourcePath = $source->getFile()
            ->getPath();
        $targetPath = $this->getManager()
            ->getTempDir()
            ->generateRandomFile($extension)
            ->getPath();

        @$image = WideImage::loadFromFile($sourcePath);

        $thumbnail = null;
        if ($s['fit'] === 'centerize') {
            $thumbnail = @$image->resize($s['width'], $s['height'], 'outside')
                ->crop('center', 'middle', $s['width'], $s['height']);
        } else {
            $thumbnail = @$image->resize($s['width'], $s['height'], $s['fit'], $s['scale']);
        }

        @$thumbnail->saveToFile($targetPath);
        @$image->destroy();
        @$thumbnail->destroy();

        $files = array(
            new File($targetPath)
        );

        return $files;
    }

    /**
     * @param array $settings
     *
     * @return array
     * @throws Exception
     */
    public function parseSettings(array $settings)
    {
        if (!isset($settings['width']) || $settings['width'] <= 0) {
            throw new Exception('Invalid thumbnail width');
        }

        if (!isset($settings['height']) || $settings['height'] <= 0) {
            throw new Exception('Invalid thumbnail height');
        }

        if (empty($settings['fit'])) {
            $settings['fit'] = 'centerize';
        }

        if (empty($settings['scale'])) {
            $settings['scale'] = 'down';
        }

        return $settings;
    }
}