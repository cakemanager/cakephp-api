<?php

namespace Api\Action;

use Api\Traits\FractalTrait;
use App\Transformer\BlogTransformer;
use Cake\ORM\TableRegistry;

class EditAction extends Action
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
        $entity = $table->patchEntity($entity, $controller->request->data());

        if ($table->save($entity)) {
            $data = $this->getItem($entity, $transformer)['data'];
            $controller->response->statusCode(200);
        } else {
            $data = $entity->errors();
            $controller->response->statusCode(400);
        }

        $controller->set('data', $data);

        $controller->set('_serialize', ['data']);
    }

}