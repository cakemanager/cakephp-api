<?php

namespace Api\Action;

use Api\Traits\FractalTrait;
use App\Transformer\BlogTransformer;
use Cake\Core\App;
use Cake\ORM\TableRegistry;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class IndexAction extends Action
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

    /**
     * Execute the index action
     *
     * @return void
     */
    protected function _execute()
    {
        $controller = $this->_controller();
        $table = $this->_table();
        $transformer = $this->_transformer();

        $entities = $table->find();

        $data = $this->getCollection($entities, $transformer);

        $controller->set('data', $data['data']);

        $controller->set('_serialize', ['data']);
    }

}