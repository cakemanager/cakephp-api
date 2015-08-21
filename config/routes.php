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
use Cake\Routing\Router;

Router::plugin('Api', ['path' => '/'], function ($routes) {
    $routes->prefix('api', function ($routes) {
        $routes->extensions(['json']);

        $routes->resources('Users', [
            'map' => [
                'token' => [
                    'action' => 'token',
                    'method' => 'POST'
                ],
                'me' => [
                    'action' => 'me',
                    'method' => 'GET'
                ]
            ]
        ]);

        $routes->fallbacks('InflectedRoute');
    });
    $routes->fallbacks('InflectedRoute');
});