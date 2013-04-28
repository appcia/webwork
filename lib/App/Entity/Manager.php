<?php

namespace App\Entity;

use Appcia\Webwork\Container;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class Manager extends EntityManager
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Make entity manager with associated container
     * Now contains DI container
     *
     * @param Container     $container    Container
     * @param array         $conn         Database configuration
     * @param Configuration $config       EM Configuration
     * @param EventManager  $eventManager Event manager
     *
     * @return Manager|EntityManager
     */
    public static function make(Container $container, array $conn, Configuration $config, EventManager $eventManager = null)
    {
        $conn = DriverManager::getConnection($conn, $config, ($eventManager ? : new EventManager()));

        $em = new self($conn, $config, $conn->getEventManager());
        $em->container = $container;

        return $em;
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Create lifecycle arguments for force calling entity callback
     *
     * @param object $object Entity
     *
     * @return Manager
     */
    public function createLifecycleEventArgs($object)
    {
        $args = new LifecycleEventArgs($object, $this);

        return $args;
    }
}