<?

namespace App\Cli\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class DataFixture extends Console\Command\Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('data-fixture:load')
            ->setDescription('Load data fixtures into database')
            ->setDefinition(array(
                new InputArgument('path', InputArgument::OPTIONAL, 'Path to fixtures')
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $em = $this->getApplication()
            ->getBootstrap()
            ->getContainer()
            ->get('em');

        if ($path === null) {
            $path = 'lib/App/DataFixture';

            $output->writeln('Loading data fixtures');
        }
        else {
            $output->writeln(sprintf("Loading data fixtures from path: '%s'", $path));
        }

        $loader = new Loader();
        $loader->loadFromDirectory($path);

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }
}