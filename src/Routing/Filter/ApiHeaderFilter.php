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
namespace Api\Routing\Filter;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Routing\DispatcherFilter;

class ApiHeaderFilter extends DispatcherFilter
{
    public function beforeDispatch(Event $event)
    {
        $request = $event->data['request'];
        $response = $event->data['response'];
        $origin = $request->header('Origin');

        $originDomains = Configure::read('Api.OriginDomains');

        $originDomains = explode(',', $originDomains);

        if (in_array('*', $originDomains)) {
            $response->header('Access-Control-Allow-Origin', '*');
        } else {
            if (in_array($origin, $originDomains)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            }
        }

        if ($request->method() == 'OPTIONS') {
            $method = $request->header('Access-Control-Request-Method');
            $headers = $request->header('Access-Control-Request-Headers');
            $response->header('Access-Control-Allow-Headers', $headers);
            $response->header('Access-Control-Allow-Methods', empty($method) ? 'GET, POST, PUT, DELETE' : $method);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '86400');
            $response->send();
        }
    }
}