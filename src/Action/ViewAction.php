<?php

namespace Api\Action;

use Api\Traits\FractalTrait;
use App\Transformer\BlogTransformer;
use Cake\ORM\TableRegistry;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class ViewAction extends Action
{

    use FractalTrait;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
    ];

    protected function _execute($vars)
    {
        $controller = $this->_controller;
        $table = $this->_table();
        $transformer = $this->_transformer();

        $entity = $table->get($vars[0]);

        $data = $this->getItem($entity, $transformer);

        $controller->set('data', $data['data']);

        $controller->set('_serialize', ['data']);
    }

}