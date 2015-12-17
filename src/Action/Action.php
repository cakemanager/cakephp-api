<?php

namespace Api\Action;

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Exception\Exception;
use Cake\Core\InstanceConfigTrait;

class Action
{

    use InstanceConfigTrait;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Used controller
     *
     * @var \Cake\Controller\Controller
     */
    protected $_controller;

    /**
     * Action constructor.
     *
     * @param \Cake\Controller\Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->_controller = $controller;
    }

    /**
     * Enable Api action
     *
     * @return void
     */
    public function enable()
    {
        $this->config('enabled', true);
    }

    /**
     * Return if an Api action has been enabled
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->config('enabled');
    }

    /**
     * Disable Api action
     *
     * @return void
     */
    public function disable()
    {
        $this->config('enabled', false);
    }

    /**
     * Execute the Api action
     *
     * @param array $arguments Arguments to be parsed.
     * @return bool
     */
    public function execute(array $arguments = [])
    {
        if (!$this->enabled()) {
            return false;
        }

        if (!is_array($arguments)) {
            $arguments = (array)$arguments;
        }

        if (method_exists($this, '_execute')) {
            return $this->_execute($arguments);
        }

        throw new Exception('Action could not be executed. _execute method does not exist.');
    }

    /**
     * Returns a Table instance
     *
     * @return \Cake\ORM\Table
     */
    protected function _table()
    {
        return $this->_controller()
            ->loadModel(null, 'Table');
    }

    /**
     * Returns a Transformer instance
     *
     * @return mixed
     */
    protected function _transformer()
    {
        $transformer = App::className('Blog', 'Transformer', 'Transformer');
        return new $transformer;
    }

    /**
     * Returns the Controller instance
     *
     * @return \Cake\Controller\Controller
     */
    protected function _controller()
    {
        return $this->_controller;
    }

}