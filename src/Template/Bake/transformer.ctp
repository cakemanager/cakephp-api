<?php
namespace <%= $namespace %>\Transformer;

use <%= $entityNamespace %>;
use League\Fractal\TransformerAbstract;

/**
 * <%= $name %> Transformer class
 */
class <%= $name %>Transformer extends TransformerAbstract
{

    public function transform(<%= $entityClass %> $<%= $entityVariable %>)
    {
        return [
    <% foreach ($columns as $column): %>
        '<%= $column %>' => $<%= $entityVariable %>-><%= $column %>,
    <% endforeach; %>
    ];
    }

}