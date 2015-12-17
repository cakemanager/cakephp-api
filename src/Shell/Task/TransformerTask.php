<?php
namespace Api\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;

class TransformerTask extends SimpleBakeTask
{
    public $pathFragment = 'Transformer/';

    public function name()
    {
        return 'transformer';
    }

    public function fileName($name)
    {
        return $name . 'Transformer.php';
    }

    public function template()
    {
        return 'Api.transformer';
    }

    public function bakeTest($className)
    {
        if (!isset($this->Test->classSuffixes[$this->name()])) {
            $this->Test->classSuffixes[$this->name()] = 'Transformer';
        }

        $name = ucfirst($this->name());
        if (!isset($this->Test->classTypes[$name])) {
            $this->Test->classTypes[$name] = 'Transformer';
        }

        return parent::bakeTest($className);
    }

}