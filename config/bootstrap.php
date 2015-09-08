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

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Routing\DispatcherFactory;

Configure::write('Api.JWT', false);

Configure::write('Api.OriginDomains', '*');

if (Configure::read('Api.settings')) {
    Configure::load('Api.settings', 'default');
}

if (Configure::read('Api.JWT')) {
    Plugin::load('ADmad/JwtAuth', []);
}

DispatcherFactory::add('Api.ApiHeader', ['priority' => 1]);