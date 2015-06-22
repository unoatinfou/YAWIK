<?php
/**
 * YAWIK
 * 
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Core\Controller\Plugin;

use Core\Entity\SnapshotGeneratorProviderInterface;
use Core\Service\SnapshotGenerator;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Stdlib\DispatchableInterface as Dispatchable;
use Zend\Stdlib\ArrayUtils;
use Core\Entity\Hydrator\EntityHydrator;

/**
 * Class EntitySnapshot
 * @package Core\Controller\Plugin
 */
class EntitySnapshot implements PluginInterface
{
    protected $serviceLocator;

    protected $repositories;

    protected $options;

    protected $entity;

    protected $generator;

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setRepositories($repositories)
    {
        $this->repositories = $repositories;
        return $this;
    }

    public function getRepositories()
    {
        return $this->repositories;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @param $entity
     * @param array $options
     */
    public function __invoke($entity = Null, $options=array()) {
        if (is_array($entity)) {
            $options = $entity;
            $entity = Null;
        }
        $this->entity = $entity;
        $this->options = $options;
        $this->generator = Null;
        if (!isset($entity)) {
            return $this;
        }
        $this->snapshot();
        return $this;
    }

    public function snapshot($entity = Null)
    {
        if (isset($entity)) {
            $this->entity = $entity;
        }
        if (!isset($this->entity)) {
            // or throw an exception ? since we expect to get a snapshot
            return $this;
        }

        if ($this->entity instanceof SnapshotGeneratorProviderInterface) {
            $serviceLocator = $this->getServicelocator();
            $generator = $this->getGenerator();
            $data = $generator->getSnapshot();

            // snapshot-class
            $target = Null;
            if (array_key_exists('target', $this->options)) {
                $target = $this->options['target'];
                if (is_string($target)) {
                    if ($serviceLocator->has($target)) {
                        $target = $serviceLocator->get($target);
                    }
                    else {
                        $target = new $target;
                    }
                    $target($data);
                }
            }

            if (isset($target)) {
                $className = get_class($this->entity);
                // @TODO, have to be abstract
                $snapShotMetaClassName = '\\' . $className . 'SnapshotMeta';
                $meta = new $snapShotMetaClassName;
                $meta->setEntity($target);
                $meta->setSourceId($this->entity->id);
                $this->getRepositories()->store($meta);
            }
        }
    }

    /**
     * shows the differences between the last snapshot and the given entity
     * return Null = there is no snapshot
     * return array() = there is a snapshot but no difference
     *
     * @param $entity
     */
    public function diff($entity)
    {
        $this->entity = $entity;
        $generator = $this->getGenerator();
        $dataHead = $generator->getSnapshot();
        if (empty($dataHead)) {
            return Null;
        }

        $className = get_class($this->entity);
        // @TODO: getting the right name for the repository is highly volatile, reminder, the name of repository is in the annotations of the entity-class
        $classNameE = explode('\\', $className);
        $repoName = $classNameE[0] . '/' . $classNameE[1];
        if (2 < count($classNameE)) {
            $repoName = $classNameE[0] . '/' . $classNameE[2];
        }
        $snapShotMetaClassName = $repoName . 'SnapshotMeta';
        $repositorySnapshotMeta = $this->getRepositories()->get($snapShotMetaClassName);
        $snapshot = $repositorySnapshotMeta->findSnapshot($this->entity);
        // an snapshot has to be so simple that there is no need for a special hydrator
        $hydrator = new EntityHydrator();
        $dataLast = $hydrator->extract($snapshot);
        if (empty($dataLast)) {
            // there is no Snapshot, but returning an empty array would make a wrong conclusion,
            // that there is a snapshot, and it has no differences.
            // actually, if there is a snapshot, it always differ (dateCreated)
            return Null;
        }
        return $this->array_compare($dataLast, $dataHead);
    }

    /**
     * the generator transforms an entity into an array
     *
     * what a generator ought to do more than an hydrator is to unriddle all related data,
     * which can imply that from other entities there also a snapshot can be created
     * @return SnapshotGenerator|mixed|null
     */
    protected function getGenerator()
    {
        if (isset($this->generator)) {
            return $this->generator;
        }

        if ($this->entity instanceof SnapshotGeneratorProviderInterface) {
            $serviceLocator = $this->getServicelocator();

            // the snapshotgenerator is a service defined by the name of the entity
            // this is the highest means, all subsequent means just add what is not set
            $className = get_class($this->entity);
            if ($serviceLocator->has('snapshotgenerator' . $className)) {
                $generator = $this->serviceLocator->get('snapshotgenerator' . $className);
                if (is_array($generator)) {
                    $this->options = ArrayUtils::merge($generator, $this->options);
                    $generator = Null;
                }
            }

            // the snapshotgenerator is provided by the entity
            // this can either be a generator-entity of a array with options
            if (!isset($generator)) {
                $generator = $this->entity->getSnapshotGenerator();
                if (is_array($generator)) {
                    // array_merge
                    $this->options = ArrayUtils::merge($generator, $this->options);
                    if (array_key_exists('generator', $generator)) {
                        $generator = $this->options['generator'];
                        unset($this->options['generator']);
                    }
                    else {
                        $generator = Null;
                    }
                }
                if (is_string($generator)) {
                    $generator = $serviceLocator->get($generator);
                }
            }

            // the last possibility to get a generator
            if (!isset($generator)) {
                // defaultGenerator
                $generator = new SnapshotGenerator();
            }

            // *** filling the options
            // hydrator
            // can be a class, but if it's a string, consider it to be an hydrator in the hydratormanager
            if (array_key_exists('hydrator', $this->options)) {
                $hydrator = $this->options['hydrator'];
                if (is_string($hydrator) && !empty($hydrator)) {
                    $hydrator = $serviceLocator->get('HydratorManager')->get($hydrator);
                }
                $generator->setHydrator($hydrator);
            }

            // exclude
            // add the elements, that should not be transferred
            if (array_key_exists('exclude', $this->options)) {
                // it is very likely that the hydrator is set by the snapshot-class,
                // so we have to asume, that may know the hydrator
                $hydrator = $generator->getHydrator();
                $exclude = $this->options['exclude'];
                if (is_array($exclude)) {
                    $hydrator->setExcludeMethods($exclude);
                }
            }
            $generator->setSource($this->entity);
            $this->generator = $generator;
        }
        return $this->generator;
    }

    /**
     * makes a recursiv difference between array1 and array2
     * found commands like  'array_diff_assoc' wanting
     *
     * the result looks like
     * key => array( old, new)
     * in subarrays it looks like
     * key.subkey = array( old, new)
     *
     * @param $array1
     * @param $array2
     */
    protected function array_compare($array1, $array2, $maxDepth = 3)
    {
        $result = array();
        $arraykeys = array_unique(array_merge(array_keys($array1), array_keys($array2)));
        foreach ($arraykeys as $key) {
            if (!empty($key) && is_string($key) && $key[0] != "\0" && substr($key,0,8) != 'Doctrine') {
                if (array_key_exists($key, $array1) && !array_key_exists($key, $array2)) {
                    $result[$key] = array($array1[$key], '');
                }
                if (!array_key_exists($key, $array1) && array_key_exists($key, $array2)) {
                    $result[$key] = array('', $array2[$key]);
                }
                if (array_key_exists($key, $array1) && array_key_exists($key, $array2)) {
                    $subResult = Null;
                    if (is_array($array1[$key]) && is_array($array2[$key])) {
                        if (0 < $maxDepth) {
                            $subResult = $this->array_compare($array1[$key], $array2[$key], $maxDepth - 1);
                        }
                    }
                    elseif (is_object($array1[$key]) && is_object($array2[$key])) {
                        if (0 < $maxDepth) {
                            $subResult = $this->array_compare((array) $array1[$key],(array) $array2[$key], $maxDepth - 1);
                        }
                    }
                    else {
                        if ($array1[$key] != $array2[$key]) {
                            $result[$key] = array( $array1[$key], $array2[$key]);
                        }
                    }
                    if (!empty($subResult)) {
                        foreach ($subResult as $subKey => $subValue) {
                            if (!empty($subKey) && is_string($subKey)) {
                                $result[$key . '.' . $subKey] = $subValue;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }


    /**
     * @param Dispatchable $controller
     */
    public function setController(Dispatchable $controller)
    {

    }

    /**
     * @return null|void|Dispatchable
     */
    public function getController(){
    }
}