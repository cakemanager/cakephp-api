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

trait AddTrait
{

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

        $model = $this->getModel();

        $entity = $model->newEntity($controller->request->data);

        // add.beforeSave event
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
            $this->setStatusCode(200);
        } else {
            $data = $entity->errors();
            $this->setStatusCode(400);
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $data);
        }

        // add.aftersave event
        if ($this->config('add.afterSave')) {
            $controller->eventManager()->on('Controller.Api.afterSave', [$controller, $this->config('add.afterSave')]);

            $event = new \Cake\Event\Event('Controller.Api.afterSave', $this, [
                'entity' => $entity,
            ]);

            $eventManager = $controller->eventManager();

            $eventManager->dispatch($event);
        }
    }

}