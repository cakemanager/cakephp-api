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
namespace Api\Testing;

use Cake\Utility\Hash;

trait ApiAssertionsTrait {

    public function assertApiResponseOk()
    {
        $this->assertResponseOk();

        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);

        $this->assertApiResponseContains('url');
        $this->assertApiResponseContains('code');
        $this->assertApiResponseContains('data');
    }

    public function assertApiResponseError()
    {
        $this->assertResponseError();

        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);

        $this->assertApiResponseContains('message');
        $this->assertApiResponseContains('url');
        $this->assertApiResponseContains('code');
    }

    public function assertApiResponseEquals($path, $expected)
    {
        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);
        $actual = Hash::get($response, $path);

        $this->assertEquals($actual, $expected);
    }

    public function assertApiNotResponseEquals($path, $expected)
    {
        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);
        $actual = Hash::get($response, $path);

        $this->assertNotEquals($actual, $expected);
    }

    public function assertApiResponseContains($path)
    {
        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);
        $actual = Hash::get($response, $path);

        $this->assertNotNull($actual);
    }

    public function assertApiNotResponseContains($path)
    {
        $response = json_decode($this->_response->body(), JSON_PRETTY_PRINT);
        $actual = Hash::get($response, $path);

        $this->assertNull($actual);
    }

    public function assertApiNotAuthenticated()
    {
        $this->assertResponseCode(302);
        $this->assertRedirect();
    }

    public function assertApiNotAuthorized()
    {
        $this->assertResponseCode(403);
        $this->assertApiResponseContains('message');
        $this->assertApiResponseContains('url');
        $this->assertApiResponseContains('code');
    }

    public function assertApiAuthorized()
    {
        $this->assertResponseCode(200);
    }

}
