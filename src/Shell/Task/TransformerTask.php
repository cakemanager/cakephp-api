<?php
namespace Api\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Core\App;
use Cake\Database\Schema\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

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

    public function bake($name)
    {
        $entityVariable = Inflector::variable($name);
        $entityClass = Inflector::classify($name);
        $entityNamespace = App::className($name, 'Model/Entity');

        $table = TableRegistry::get(Inflector::pluralize($name));
        $columns = $table->schema()->columns();

        $this->BakeTemplate->set('entityVariable', $entityVariable);
        $this->BakeTemplate->set('entityClass', $entityClass);
        $this->BakeTemplate->set('entityNamespace', $entityNamespace);
        $this->BakeTemplate->set('columns', $columns);

//        debug($name);
//        debug($this->BakeTemplate->viewVars);
//        die;

        parent::bake($name);
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