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
namespace Api\Test\TestCase\Controller\Component;

use Api\Controller\Component\ApiBuilderComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;

/**
 * Api\Controller\Component\ApiBuilderComponent Test Case
 */
class ApiBuilderComponentTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.api.blogs'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new Request();
        $response = new Response();

        $this->Controller = $this->getMock('Cake\Controller\Controller', ['redirect'], [$request, $response]);

        $registry = new ComponentRegistry($this->Controller);

        $this->ApiBuilder = new ApiBuilderComponent($registry);

        $this->ApiBuilder->setController($this->Controller);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ApiBuilder);

        parent::tearDown();
    }

    /**
     * testSetController
     *
     * @return void
     */
    public function testSetController()
    {
        $this->assertNotEmpty($this->ApiBuilder->Controller);

        $this->ApiBuilder->setController('fake');

        $this->assertEquals('fake', $this->ApiBuilder->Controller);
    }

    /**
     * testAddParentResource
     *
     * @return void
     */
    public function testAddParentResource()
    {
        $this->assertEmpty($this->ApiBuilder->config('resources'));

        $this->ApiBuilder->addParentResource('Users', 'user_id');

        $this->assertArrayHasKey('Users', $this->ApiBuilder->config('resources'));
        $this->assertEquals('user_id', $this->ApiBuilder->config('resources.Users'));
    }

    /**
     * testEnableSingle
     *
     * @return void
     */
    public function testEnableSingle()
    {
        $this->assertEmpty($this->ApiBuilder->config('actions'));

        $this->ApiBuilder->enable('index');

        $this->assertArrayHasKey('index', $this->ApiBuilder->config('actions'));
        $this->assertEquals(true, $this->ApiBuilder->config('actions.index'));

        $this->ApiBuilder->enable('view', [
            'key' => 'value'
        ]);

        $this->assertEquals(true, $this->ApiBuilder->config('actions.view'));

        $this->assertNotEmpty($this->ApiBuilder->config('view'));
    }

    /**
     * testEnableMultiple
     *
     * @return void
     */
    public function testEnableMuliple()
    {
        $this->assertEmpty($this->ApiBuilder->config('actions'));

        $this->ApiBuilder->enable(['index', 'view']);

        $this->assertArrayHasKey('index', $this->ApiBuilder->config('actions'));
        $this->assertArrayHasKey('view', $this->ApiBuilder->config('actions'));
        $this->assertEquals(true, $this->ApiBuilder->config('actions.index'));
        $this->assertEquals(true, $this->ApiBuilder->config('actions.view'));
    }

    /**
     * testDisableSingle
     *
     * @return void
     */
    public function testDisableSingle()
    {
        $this->assertEmpty($this->ApiBuilder->config('actions'));

        $this->ApiBuilder->enable('index');

        $this->ApiBuilder->disable('index');

        $this->assertEquals(false, $this->ApiBuilder->config('actions.index'));
    }

    /**
     * testDisableMultiple
     *
     * @return void
     */
    public function testDisableMultiple()
    {
        $this->assertEmpty($this->ApiBuilder->config('actions'));

        $this->ApiBuilder->enable(['index', 'view']);

        $this->ApiBuilder->disable(['index', 'view']);

        $this->assertEquals(false, $this->ApiBuilder->config('actions.index'));
        $this->assertEquals(false, $this->ApiBuilder->config('actions.view'));
    }

    /**
     * testActionIsset
     *
     * @return void
     */
    public function testActionIsset()
    {
        $this->assertFalse($this->ApiBuilder->actionIsset('index'));

        $this->ApiBuilder->enable('index');

        $this->assertTrue($this->ApiBuilder->actionIsset('index'));

        $this->ApiBuilder->disable('index');

        $this->assertFalse($this->ApiBuilder->actionIsset('index'));
    }

    /**
     * testSerialize
     *
     * @return void
     */
    public function testSerialize()
    {
        $expected = [
            'message',
            'url',
            'code',
            'data'
        ];

        $this->assertEquals($expected, $this->ApiBuilder->config('_serialize'));

        $this->ApiBuilder->serialize('testVariable');

        $expected = [
            'message',
            'url',
            'code',
            'data',
            'testVariable'
        ];

        $this->assertEquals($expected, $this->ApiBuilder->config('_serialize'));
    }

    /**
     * testExecuteAcion
     *
     * @return void
     */
    public function testExecuteAction()
    {
        $this->ApiBuilder->config('modelName', 'Blogs');

        $this->assertFalse($this->ApiBuilder->executeAction('nonExistingAction'));

        $this->assertTrue($this->ApiBuilder->executeAction('index'));
    }

    /**
     * testFindAll
     *
     * @return void
     */
    public function testFindAll()
    {
        $this->ApiBuilder->config('modelName', 'Blogs');

        $this->assertEquals(10, count($this->ApiBuilder->findAll()));

        $this->ApiBuilder->Controller->request->params['user_id'] = 1;

        $this->ApiBuilder->addParentResource('Users', 'user_id');

        $this->assertEquals(1, count($this->ApiBuilder->findAll()));

        $this->assertInstanceOf('\Cake\ORM\Query', $this->ApiBuilder->findAll(['toArray' => false]));
        $this->assertNotInstanceOf('\Cake\ORM\Query', $this->ApiBuilder->findAll(['toArray' => true]));
    }

    /**
     * testFindSingle
     *
     * @return void
     */
    public function testFindSingle()
    {
        $this->ApiBuilder->config('modelName', 'Blogs');

        $this->assertNotEmpty($this->ApiBuilder->findSingle(2));

        $this->ApiBuilder->Controller->request->params['user_id'] = 1;

        $this->assertNotEmpty($this->ApiBuilder->findSingle(2));

        $this->ApiBuilder->addParentResource('Users', 'user_id');

        $this->assertNotEmpty($this->ApiBuilder->findSingle(1));

        $this->assertNotInstanceOf('\Cake\ORM\Entity', $this->ApiBuilder->findSingle(1));

        $this->assertInstanceOf('\Cake\ORM\Entity', $this->ApiBuilder->findSingle(1, ['toArray' => false]));
    }
}
