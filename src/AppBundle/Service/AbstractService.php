<?php

namespace AppBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ManagerAbstract
 *
 * @author Christian Schätzle <christian.schaetzle@huethig.de>
 */
abstract class AbstractService
{
    /**
     * @var  EntityManager
     */
    private $manager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var string
     */
    protected $class;

    /*
     * @param ObjectManager $manager
     *
     */
    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher, $class)
    {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository($class);
        $this->dispatcher = $dispatcher;

        $metadata    = $manager->getClassMetadata($class);
        $this->class = $metadata->name;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Finds one by the given criteria
     *
     * @param array $criteria
     *
     * @return mixed
     */
    public function findOneByCriteria(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    public function getAll()
    {
        return $this->getRepository()->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

}
