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

use Settings\Core\Setting;
use Cake\Core\Configure;

Configure::write('Settings.Prefixes.Api', 'API');


Setting::register('Api.JWT', 0, [
    'type' => 'select',
    'options' => [
        1 => 'On',
        0 => 'Off'
    ]
]);

return [
    'Api.JWT' => Setting::read('Api.JWT'),
];