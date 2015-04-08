<?php
/**
 * CakeManager (http://cakemanager.org)
 * Copyright (c) http://cakemanager.org
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) http://cakemanager.org
 * @link          http://cakemanager.org CakeManager Project
 * @since         1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Api\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Exception\Exception;
use Cake\Utility\Inflector;

/**
 * ApiBuilder component
 */
class ApiBuilderComponent extends Component
{
    /**
     * Default configuration.
     *
     * ### Options
     *
     * - `modelName` - the model to use from the controller (string)
     * - `serialze` - the variables to serialize.
     * - `index` - contains settings of the `index`-method
     *
     * @var array
     */
    protected $_defaultConfig = [
        'modelName' => null,
        '_serialize' => [
            'message',
            'url',
            'code',
            'data'
        ],
        'resources' => [
        ],
        'actions' => [
        ],
        'index' => [
            'beforeFind' => false,
        ],
        'view' => [
            'beforeFind' => false,
        ],
        'add' => [
            'messageOnSuccess' => 'The {0} has been saved.',
            'messageOnError' => 'The {0} could not be saved.',
            'beforeSave' => false,
        ],
        'edit' => [
            'messageOnSuccess' => 'The {0} has been upated.',
            'messageOnError' => 'The {0} could not be updated.',
            'beforeSave' => false,
        ],
        'delete' => [
            'messageOnSuccess' => 'The {0} has been deleted.',
            'messageOnError' => 'The {0} could not be deleted.',
        ]
    ];

    /**
     * Controller
     *
     * @var \Cake\Controller\Controller
     */
    protected $Controller = null;

    /**
     * setController
     *
     * Setter for the Controller param.
     *
     * @param Controller $controller Controller.
     * @return void
     */
    public function setController($controller)
    {
        $this->Controller = $controller;
    }

    /**
     * initialize
     *
     * @param array $config Config.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setController($this->_registry->getController());

        // adding request handler
        $this->Controller->loadComponent('RequestHandler');

        // accepts json
        $this->Controller->request->accepts('application/json');

        // set the default modelName
        if (is_null($this->config('modelName'))) {
            $this->config('modelName', $this->Controller->name);
        }
    }

    /**
     * addParentResource
     *
     * Method to add a resource who's parent of the current one.
     * Registering this resource will affect the query.
     *
     * @param string $name Name of the resource.
     * @param string $variable Variable name of the resource (like `article_id`)
     * @return void
     */
    public function addParentResource($name, $variable)
    {
        $this->config('resources.' . $name, $variable);
    }

    /**
     * enable
     *
     * Enables actions for api.
     *
     * @param string|array $actions The action/actions to enable.
     * @param array|null $options Options for the chosen action.
     * @return void
     */
    public function enable($actions, $options = [])
    {
        if (is_array($actions)) {
            foreach ($actions as $action => $options) {
                if (is_array($options)) {
                    $this->enable($action, $options);
                } else {
                    $this->enable($options);
                }
            }
            return;
        }

        $action = $actions;

        $this->config('actions.' . $action, true);

        $this->config($action, $options);
    }

    /**
     * disable
     *
     * Disables actions for api.
     *
     * @param string|array $actions The action/actions to disable.
     * @return void
     */
    public function disable($actions)
    {
        if (is_array($actions)) {
            foreach ($actions as $action) {
                $this->disable($action);
            }
            return;
        }

        $action = $actions;

        $this->config('actions.' . $action, false);
    }

    /**
     * actionIsset
     *
     * Checks if a specific action is enabled.
     *
     * @param string $action Action to check for.
     * @return bool
     */
    public function actionIsset($action)
    {
        return (!is_null($this->config('actions.' . $action)) ? $this->config('actions.' . $action) : false);
    }

    /**
     * serialize
     *
     * Method to add params that should be serialized.
     *
     * ### Example
     *
     * `$this->ApiBuilder->serialize(['data1', 'data2']);
     * `$this->ApiBuilder->serialize('data3');
     *
     * This example will serialize `data1`, `data2`, `data3` if set.
     *
     * @param array|string $data Strings to serialize.
     * @return void
     */
    public function serialize($data)
    {
        $data = (array)$data;

        $_data = $this->config('_serialize');

        $data = array_merge($_data, $data);

        $this->config('_serialize', $data, false);
    }

    /**
     * executeAction
     *
     * Executes the requested action.
     *
     * @param string $action Action to execute.
     * @param array $options Options to use and send.
     * @return bool
     */
    public function executeAction($action, $options = [])
    {
        $methodName = '__' . lcfirst($action) . 'Action';

        if (method_exists($this, $methodName)) {
            return $this->$methodName($options);
        } else {
            return false;
        }
    }

    /**
     * _viewVarExists
     *
     * Small helper to check if a view variable is set.
     *
     * @param string $variable Variable name.
     * @return bool
     */
    protected function _viewVarExists($variable)
    {
        return key_exists($variable, $this->Controller->viewVars);
    }

    /**
     * findAll
     *
     * Method to query a list (for the index-action). This method will
     * do a `where` on the used resources.
     *
     * ### Options
     *
     * - resources - True if resources should be taken in the queries. (So putting a where on the value)
     * - toArray - If this method should return an array or the Query object
     *
     * @param array $options Options to pass thru.
     * @return \Cake\ORM\Query|array
     */
    public function findAll($options = [])
    {
        $_options = [
            'resources' => true,
            'toArray' => true
        ];

        $options = array_merge($_options, $options);

        $modelName = $this->config('modelName');
        $model = \Cake\ORM\TableRegistry::get($modelName);

        $query = $model->find('all');

        if ($options['resources']) {
            foreach ($this->config('resources') as $resource => $field) {
                $key = $modelName . '.' . $field;
                $value = $this->Controller->request->params[$field];

                $query->where([$key => $value]);
            }
        }

        if ($options['toArray']) {
            return $query->toArray();
        }

        return $query;
    }

    /**
     * findSingle
     *
     * Method to query a single item (for the view-action). This method will
     * do a `where` on the used resources.
     *
     * @param int $id Id of the item.
     * @param array $options Options to pass thru.
     * @return \Cake\ORM\Query
     */
    public function findSingle($id, $options = [])
    {
        $_options = [
            'resources' => true,
            'toArray' => true
        ];

        $options = array_merge($_options, $options);
        $exists = [];

        $modelName = $this->config('modelName');
        $model = \Cake\ORM\TableRegistry::get($modelName);

        $pk = $modelName . '.id';
        $query = $model->find('all')->where([$pk => $id]);
        $exists[$pk] = $id;

        if ($options['resources']) {
            foreach ($this->config('resources') as $resource => $field) {
                $key = $modelName . '.' . $field;
                $value = $this->Controller->request->params[$field];

                $query->where([$key => $value]);
                $exists[$key] = $value;
            }
        }

        if ($options['toArray']) {
            return $query->firstOrFail()->toArray();
        }

        return $query->firstOrFail();
    }

    /**
     * __indexAction
     *
     * Execute action for the index-action
     *
     * @return void
     */
    private function __indexAction()
    {
        $controller = $this->Controller;

        $query = $this->findAll(['toArray' => false]);

        if ($this->config('index.beforeFind')) {
            $controller->eventManager()->on('Controller.Api.beforeFind', [$controller, $this->config('index.beforeFind')]);

            $event = new \Cake\Event\Event('Controller.Api.beforeFind', $this, [
                'query' => $query,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);

            if (!is_null($event->result['query'])) {
                $query = $event->result['query'];
            }
        }

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode());
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $query->toArray());
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }

    /**
     * __viewAction
     *
     * Execute action for the view-action
     *
     * @return void
     */
    private function __viewAction()
    {
        $controller = $this->Controller;

        $id = $controller->passedArgs[0];

        $query = $this->findSingle($id, ['toArray' => false]);

        if ($this->config('view.beforeFind')) {
            $controller->eventManager()->on('Controller.Api.beforeFind', [$controller, $this->config('view.beforeFind')]);

            $event = new \Cake\Event\Event('Controller.Api.beforeFind', $this, [
                'query' => $query,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);

            if (!is_null($event->result['query'])) {
                $query = $event->result['query'];
            }
        }

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode());
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $query->toArray());
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }

    /**
     * __addAction
     *
     * Execute action for the add-action
     *
     * @return void
     */
    private function __addAction()
    {
        $controller = $this->Controller;

        $modelName = $this->config('modelName');
        $model = $this->Controller->{$modelName};

        $entity = $model->newEntity($controller->request->data);

        if ($this->config('add.beforeSave')) {
            $controller->eventManager()->on('Controller.Api.beforeSave', [$controller, $this->config('add.beforeSave')]);

            $event = new \Cake\Event\Event('Controller.Api.beforeSave', $this, [
                'entity' => $entity,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);

            if (!is_null($event->result['entity'])) {
                $entity = $event->result['entity'];
            }
        }

        if ($model->save($entity)) {
            $data = $model->get($entity->get('id'));
            $message = __($this->config('add.messageOnSuccess'), Inflector::singularize($modelName));
            $statusCode = 200;
        } else {
            $data = $entity->errors();
            $message = __($this->config('add.messageOnError'), Inflector::singularize(lcfirst($modelName)));
            $statusCode = 400;
        }

        // set message variable
        if (!$this->_viewVarExists('message')) {
            $controller->set('message', $message);
        }

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode($statusCode));
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $data);
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }

    /**
     * __editAction
     *
     * Execute action for the add-action
     *
     * @return void
     */
    private function __editAction()
    {
        $controller = $this->Controller;

        $id = $controller->passedArgs[0];

        $modelName = $this->config('modelName');
        $model = $this->Controller->{$modelName};

        $entity = $this->findSingle($id);

        $entity = $model->patchEntity($entity, $controller->request->data);

        if ($this->config('edit.beforeSave')) {
            $controller->eventManager()->on('Controller.Api.beforeSave', [$controller, $this->config('edit.beforeSave')]);

            $event = new \Cake\Event\Event('Controller.Api.beforeSave', $this, [
                'entity' => $entity,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);

            if (!is_null($event->result['entity'])) {
                $entity = $event->result['entity'];
            }
        }

        if ($model->save($entity)) {
            $data = $model->get($entity->get('id'));
            $message = __($this->config('edit.messageOnSuccess'), Inflector::singularize($modelName));
            $statusCode = 200;
        } else {
            $data = $entity->errors();
            $message = __($this->config('edit.messageOnError'), Inflector::singularize(lcfirst($modelName)));
            $statusCode = 400;
        }

        // set message variable
        if (!$this->_viewVarExists('message')) {
            $controller->set('message', $message);
        }

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode($statusCode));
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $data);
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }

    /**
     * __deleteAction
     *
     * Execute action for the add-action
     *
     * @return void
     */
    private function __deleteAction()
    {
        $controller = $this->Controller;

        $id = $controller->passedArgs[0];

        $modelName = $this->config('modelName');
        $model = $this->Controller->{$modelName};

        $entity = $this->findSingle($id);

        if ($model->delete($entity)) {
            $message = __($this->config('delete.messageOnSuccess'), Inflector::singularize($modelName));
            $statusCode = 200;
        } else {
            $message = __($this->config('delete.messageOnError'), Inflector::singularize(lcfirst($modelName)));
            $statusCode = 400;
        }

        // set message variable
        if (!$this->_viewVarExists('message')) {
            $controller->set('message', $message);
        }

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode($statusCode));
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }
}
