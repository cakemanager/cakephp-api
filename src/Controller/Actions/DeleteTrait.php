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

        $modelName = $this->config('modelName');
        $model = $this->Controller->{$modelName};

        $entity = $this->findSingle($id, ['toArray' => false]);

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
    }

}