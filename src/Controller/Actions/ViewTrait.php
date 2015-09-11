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

trait ViewTrait
{

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

        // set data variable
        if (!$this->_viewVarExists('data')) {
            $controller->set('data', $query->first()->toArray());
        }
    }

}