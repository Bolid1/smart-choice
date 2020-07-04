<?php

declare(strict_types=1);

namespace App\Doctrine\Gedmo\Tree;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Exception\UnexpectedValueException;

/**
 * Waiting for.
 *
 * @see https://github.com/Atlantic18/DoctrineExtensions/pull/2146
 */
class TreeListener extends \Gedmo\Tree\TreeListener
{
    /**
     * Tree processing strategies for object classes.
     *
     * @var array
     */
    private array $strategies = [];

    /**
     * List of strategy instances.
     *
     * @var array
     */
    private array $strategyInstances = [];

    public function getStrategy(ObjectManager $om, $class)
    {
        if (!isset($this->strategies[$class])) {
            $config = $this->getConfiguration($om, $class);
            if (!$config) {
                throw new UnexpectedValueException(
                    "Tree object class: {$class} must have tree metadata at this point"
                );
            }
            $managerName = 'UnsupportedManager';
            if ($om instanceof EntityManagerInterface) {
                $managerName = 'ORM';
            }/* elseif ($om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
                $managerName = 'ODM\\MongoDB';
            }*/

            if (!isset($this->strategyInstances[$config['strategy']])) {
                $strategyName = \ucfirst($config['strategy']);
                if ('Nested' === $strategyName) {
                    $strategyClass = Strategy\ORM\Nested::class;
                } else {
                    $strategyClass = $this->getNamespace().'\\Strategy\\'.$managerName.'\\'.$strategyName;
                }

                if (!\class_exists($strategyClass)) {
                    throw new InvalidArgumentException(
                        $managerName." TreeListener does not support tree type: {$config['strategy']}"
                    );
                }
                $this->strategyInstances[$config['strategy']] = new $strategyClass($this);
            }
            $this->strategies[$class] = $config['strategy'];
        }

        return $this->strategyInstances[$this->strategies[$class]];
    }

    protected function getStrategiesUsedForObjects(array $classes): array
    {
        $strategies = [];
        foreach ($classes as $name => $opt) {
            if (isset($this->strategies[$name]) && !isset($strategies[$this->strategies[$name]])) {
                $strategies[$this->strategies[$name]] = $this->strategyInstances[$this->strategies[$name]];
            }
        }

        return $strategies;
    }
}
