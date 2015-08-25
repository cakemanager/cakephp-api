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

use Api\Controller\Actions\AddTrait;
use Api\Controller\Actions\DeleteTrait;
use Api\Controller\Actions\EditTrait;
use Api\Controller\Actions\IndexTrait;
use Api\Controller\Actions\ViewTrait;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Utility\Inflector;

/**
 * ApiBuilder component
 */
class ApiBuilderComponent extends Component
{

    use IndexTrait;
    use ViewTrait;
    use AddTrait;
    use EditTrait;
    use DeleteTrait;

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

        if (Configure::read('Api.JWT')) {
            debug('jwt');
            if ($this->Controller->Auth) {
                $this->Controller->Auth->config('authenticate', [
                    'ADmad/JwtAuth.Jwt' => [
                        'parameter' => '_token',
                        'userModel' => 'Users.Users',
                        'scope' => ['Users.active' => 1],
                        'fields' => [
                            'id' => 'id'
                        ]
                    ]
                ]);
            }
        }

    }

    /**
     * execute
     *
     * Executes the request.
     *
     * ### Example:
     *
     * // running your own action:
     *      public function customAction() {
     *          $this->set('data', $data);
     *          return $this->ApiBuilder->execute();
     *      }
     *
     * // running any pre-defined crud-actions:
     *      public function add() {
     *          return $this->ApiBuilder->execute('add');
     *      }
     *
     * @param string|void $action Action to execute.
     * @param array $options Options.
     * @return bool
     */
    public function execute($action = null, $options = [])
    {
        if ($action) {
            $methodName = '__' . lcfirst($action) . 'Action';

            if (method_exists($this, $methodName)) {
                $this->$methodName($options);
            } else {
                return false;
            }
        }

        $controller = $this->Controller;

        // set url variable
        if (!$this->_viewVarExists('url')) {
            $controller->set('url', $controller->request->here());
        }

        // set code variable
        if (!$this->_viewVarExists('code')) {
            $controller->set('code', $controller->response->statusCode());
        }

        // serialize
        $controller->set('_serialize', $this->config('_serialize'));
    }


    /**
     * addParentResource
     *
     * Method to add a resource who's parent of the current one.
     * Registering this resource will affect the query.
     *
     * ### Example:
     *
     * $this->ApiBuilder->addparentResource('Articles', 'article_id');
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
     * Enables specific actions for api.
     *
     * ### Example:
     * // single action
     *      $this->ApiBuilder->enable('index');
     *
     * // multiple actions
     *      $this->ApiBuilder->enable(['index', 'view']);
     *
     * @param string|array $actions The action/actions to enable.
     * @param array|null $options Options for the chosen action.
     * @return void
     */
    public function enable($actions)
    {
        if (is_array($actions)) {
            foreach ($actions as $action) {
                $this->enable($action);
            }
            return;
        }
        $_actions = $this->config('actions');
        $_actions[] = $actions;
        $this->config('actions', $_actions);
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

        $_actions = $this->config('actions');
        foreach ($_actions as $key => $value) {
            if ($value == $actions) {
                unset($_actions[$key]);
            }
        }
        $this->config('actions', $_actions, false);
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
        $actions = $this->config('actions');
        if (in_array($action, $actions)) {
            return true;
        }
        return false;
    }

    public function setStatusCode($code)
    {
        $controller = $this->Controller;
        $controller->response->statusCode($code);
    }

    /**
     * serialize
     *
     * Method to add params that should be serialized.
     *
     * ### Example
     *
     * // single param
     *      $this->ApiBuilder->serialize('data3');
     * // multiple params
     *      $this->ApiBuilder->serialize(['data1', 'data2']);
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
     * getModel
     *
     * Returns the used model.
     *
     * @return Model
     */
    public function getModel()
    {
        $modelName = $this->config('modelName');
        $model = \Cake\ORM\TableRegistry::get($modelName);

        if ($model) {
            return $model;
        }
        return false;
    }

}
