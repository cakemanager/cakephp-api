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
        $execute = $this->ApiBuilder->executeAction($request->params['action']);
        if ($execute !== false) {
            return $execute;
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

        if ($this->ApiBuilder->actionIsset($action)) {
            return true;
        }

        return false;
    }
}
