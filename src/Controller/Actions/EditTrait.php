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

trait EditTrait
{

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

        $model = $this->getModel();

        $entity = $this->findSingle($id, ['toArray' => false]);

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
            $statusCode = 200;
        } else {
            $data = $entity->errors();
            $statusCode = 400;
        }

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $data);
        }
    }

}