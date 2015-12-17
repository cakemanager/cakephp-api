<?php
namespace Api\Traits;

use Cake\Datasource\EntityInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

trait FractalTrait
{
    /**
     * Instance of the Fractal manager
     *
     * @var \League\Fractal\Manager
     */
    protected $_fractalManager;

    /**
     * Returns an instance of the Fractal manager.
     * If there is no instance, a new instance will be created.
     *
     * @return \League\Fractal\Manager
     */
    public function fractalManager()
    {
        if (!$this->_fractalManager) {
            $this->_fractalManager = new Manager();
        }

        return $this->_fractalManager;
    }

    /**
     * @param mixed $data
     * @param \League\Fractal\TransformerAbstract $transformer
     *
     * @return array
     */
    public function getCollection($data, TransformerAbstract $transformer)
    {
        $resource = new Collection($data, new $transformer());

        return $this->fractalManager()->createData($resource)->toArray();
    }

    /**
     * @param \Cake\Datasource\EntityInterface $data
     * @param \League\Fractal\TransformerAbstract $transformer
     *
     * @return array
     */
    public function getItem(EntityInterface $data, TransformerAbstract $transformer)
    {
        $resource = new Item($data, new $transformer());

        return $this->fractalManager()->createData($resource)->toArray();
    }

}