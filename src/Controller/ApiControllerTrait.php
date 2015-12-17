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
namespace Api\Controller;

use Cake\Controller\Exception\MissingActionException;

/**
 * ApiControllerTrait
 */
trait ApiControllerTrait
{

    /**
     * List of components that are capable of dispatching an action that is
     * not already implemented
     *
     * @var array
     */
    public $dispatchComponents = [];

    /**
     * Dispatches the controller action. Checks that the action exists and isn't private.
     *
     * @return mixed The resulting response.
     * @throws \LogicException When request is not set.
     * @throws \Cake\Controller\Exception\MissingActionException When actions are not defined or set
     */
    public function invokeAction()
    {
        $request = $this->request;
        if (!isset($request)) {
            throw new \LogicException('No Request object configured. Cannot invoke action');
        }
        if (!$this->isAction($request->params['action'])) {
            throw new MissingActionException([
                'controller' => $this->name . 'Controller',
                'action' => $request->params['action'],
                'prefix' => isset($request->params['prefix']) ? $request->params['prefix'] : '',
                'plugin' => $request->params['plugin'],
            ]);
        }
        $callable = [$this, $request->params['action']];
        if (is_callable($callable)) {
            return call_user_func_array($callable, $request->params['pass']);
        }

        // Adding our own stuff to generate the action
        $component = $this->_isActionMapped();
        if ($component) {
            return $component->execute();
        }

        throw new MissingActionException([
            'controller' => $this->name . 'Controller',
            'action' => $request->params['action'],
            'prefix' => isset($request->params['prefix']) ? $request->params['prefix'] : '',
            'plugin' => $request->params['plugin'],
        ]);
    }

    /**
     * Return true for a mapped action so that AuthComponent doesn't skip
     * authentication / authorization for that action.
     *
     * @param string $action Action name.
     * @return bool True is action is mapped and enabled.
     */
    public function isAction($action)
    {
        $isAction = parent::isAction($action);

        if ($isAction) {
            return true;
        }

        if ($this->ApiBuilder->enabled($action)) {
            return true;
        }

        return false;
    }

    /**
     * Check if an action can be dispatched using CRUD.
     *
     * @return bool|\Cake\Controller\Component The component instance if action is
     *  mapped else `false`.
     */
    protected function _isActionMapped()
    {
        if (!empty($this->dispatchComponents)) {
            foreach ($this->dispatchComponents as $component => $enabled) {
                if (empty($enabled)) {
                    continue;
                }
                // Skip if isActionMapped isn't defined in the Component
                if (!method_exists($this->{$component}, 'isActionMapped')) {
                    continue;
                }
                // Skip if the action isn't mapped
                if (!$this->{$component}->isActionMapped()) {
                    continue;
                }
                // Skip if execute isn't defined in the Component
                if (!method_exists($this->{$component}, 'execute')) {
                    continue;
                }
                // Return the component instance.
                return $this->{$component};
            }
        }
        return false;
    }
}
