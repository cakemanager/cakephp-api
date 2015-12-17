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
use Cake\Controller\ComponentRegistry;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Network\Response;
use Cake\Utility\Inflector;

/**
 * ApiBuilder component
 */
class ApiBuilderComponent extends Component
{

    /**
     * Controller
     *
     * @var \Cake\Controller\Controller
     */
    protected $_controller;

    /**
     * EventManager
     *
     * @var \Cake\Event\EventManager
     */
    protected $_eventManager;

    /**
     * Current action
     *
     * @var string
     */
    protected $_action;

    /**
     * Array of instances of actions.
     *
     * @var array
     */
    protected $_actions;

    /**
     * Default configuration
     *
     * Options:
     * - `actions` - Actions that should be build.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => [],
        'listeners' => []
    ];

    /**
     * Constructor
     *
     */
    public function __construct(ComponentRegistry $collection, $config = [])
    {
        $this->_controller = $collection->getController();
        $this->_controller->loadComponent('RequestHandler');

        $this->_eventManager = $this->_controller->eventManager();

        parent::__construct($collection, $config);
    }

    /**
     * beforeFilter event.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeFilter($event)
    {
        $this->_action = $this->_controller->request->action;

        if (!isset($this->_controller->dispatchComponents)) {
            $this->_controller->dispatchComponents = [];
        }
        $this->_controller->dispatchComponents['ApiBuilder'] = true;

//        $this->_loadListeners();

//        $this->trigger('beforeFilter');
    }

    /**
     * Check if an Api action has been mapped
     *
     * @param string $action If null, use the current action.
     * @return bool
     */
    public function isActionMapped($action = null)
    {
        if (!$action) {
            $action = $this->_action;
        }
        $action = Inflector::variable($action);
        $actionConfig = $this->config('actions.' . $action);
        if (!$actionConfig) {
            return false;
        }
        return $this->action($action)->config('enabled');
    }

    /**
     * Get an ApiAction object by action the name.
     *
     * @param string $name Action name.
     * @return void
     */
    public function action($name = null)
    {
        if (!$name) {
            $name = $this->_action;
        }
        $name = Inflector::variable($name);
        return $this->_loadAction($name);
    }

    /**
     * Enable one or multiple Api actions.
     *
     * @param string|array $actions Array of actions to enable, or string with the name of action to enable.
     * @return void
     */
    public function enable($actions)
    {
        foreach ((array)$actions as $action) {
            $this->action($action)->enable();
        }
    }

    /**
     * Disable one or multiple Api actions.
     *
     * @param string|array $actions Array of actions to disable, or string with the name of action to disable.
     * @return void
     */
    public function disable($actions)
    {
        foreach ((array)$actions as $action) {
            $this->action($action)->disable();
        }
    }

    public function enabled($action)
    {
        return true;
    }

    public function execute($action = null, $arguments = [])
    {
        $this->_action = $action ?: $this->_action;
        $action = $this->_action;

        if (!$arguments) {
            $arguments = $this->_controller->request->params['pass'];
        }

        try {
            $response = $this->action($action)->execute($arguments);
            if ($response instanceof Response) {
                return $response;
            }
        } catch (Exception $e) {
            if (isset($e->response)) {
                return $e->response;
            }
            throw $e;
        }

        return $this->_controller->response = $this->_controller->render(null);

        // @todo return view stuff
    }

    /**
     * Load an Api action instance
     *
     * @param string $name Api action name
     */
    protected function _loadAction($name)
    {
        if (!isset($this->_actions[$name])) {
            $config = $this->config('actions.' . $name);

            $className = App::classname($config, 'Action', 'Action');

            if (!$className) {
                throw new Exception('Api Action not found');
            }

            $this->_actions[$name] = new $className($this->_controller);
        }

        return $this->_actions[$name];
    }

    /**
     * Triggers an event.
     *
     * @param \Cake\Event\Event $event Event
     * @param mixed $subject
     * @return \Cake\Event\Event
     */
    public function trigger($event, $subject)
    {
        $Subject = $subject ?: $this->getSubject();
        $Subject->addEvent($event);

        $Event = new Event($event, $Subject);
        $this->_eventManager->dispatch($Event);

        if ($Event->result instanceof Response) {
            $Exception = new Exception();
            $Exception->response = $Event->result;
            throw $Exception;
        }

        return $Event;
    }

    /**
     * Attaches an eventlistener on the eventmanager.
     *
     * @param string|array $events Events
     * @param callback $callback Callable method to be executed.
     * @param array $options Options for the event.
     * @return void
     */
    public function on($events, $callback, $options = [])
    {
        foreach ((array)$events as $event) {
            $this->_eventManager->on($event, $options, $callback);
        }
    }

    /**
     * Get a registered eventlistener.
     *
     * @param string $name Listener
     */
    public function listener($name)
    {
        return $this->_loadListener($name);
    }

    public function addListener($name, $className = null, $config = [])
    {
        if (strpos($name, '.') !== false) {
            list($plugin, $name) = pluginSplit($name);
            $className = $plugin . '.' . Inflector::camelize($name);
        }
        $name = Inflector::variable($name);
        $this->config(sprintf('listeners.%s', $name), compact('className') + $config);
    }

}
