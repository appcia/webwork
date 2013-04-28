<?

namespace App\Cli;

use App\Cli\Command;
use Appcia\Webwork\Bootstrap;
use Symfony\Component\Console\Application;

class App extends Application
{
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * Constructor
     */
    public function __construct(Bootstrap $bootstrap) {
        parent::__construct('Skeleton CLI', '1.0');

        $this->bootstrap = $bootstrap;

        $this->addCommands(array(
            new Command\DataFixture()
        ));
    }

    /**
     * Get bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }
}