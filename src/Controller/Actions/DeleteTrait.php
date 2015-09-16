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

namespace Api\Controller\Actions;

trait DeleteTrait
{

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

        $model = $this->getModel();

        $entity = $this->findSingle($id, ['toArray' => false])->first();

        if ($this->config('delete.beforeDelete')) {
            $controller->eventManager()->on('Controller.Api.beforeDelete', [$controller, $this->config('edit.beforeDelete')]);

            $event = new \Cake\Event\Event('Controller.Api.beforeDelete', $this, [
                'entity' => $entity,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);

            if (!is_null($event->result['entity'])) {
                $entity = $event->result['entity'];
            }
        }

        if ($model->delete($entity)) {
            $statusCode = 200;
        } else {
            $statusCode = 400;
        }

        // set message variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $entity);
        }
    }

}