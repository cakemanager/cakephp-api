# Api plugin for CakePHP

This plugin allows you to easily create flexible restful api's on the fly.

> Note: This is a non-stable plugin for CakePHP 3.0 at this time. It is currently under development and should be 
considered experimental. We are working hard for a first release!

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require cakemanager/cakephp-api:dev-master
```

Now you need to load the plugin with:

```
$ bin/cake plugin load -b -r Api
```

## Usage

The plugin makes use of `Transformer`-classes. In fact Transformers are the view-layer of your API.

Transformer example located in `src/Transformer/BlogTransformer`:

```
<?php
namespace App\Transformer;

use App\Model\Entity\Blog;
use League\Fractal\TransformerAbstract;

/**
 * Blog Transformer
 */
class BlogTransformer extends TransformerAbstract
{

    public function transform(Blog $blog)
    {
        return [
            'id' => (int)$blog->id,
            'title' => $blog->title,
            'body' => $blog->body,
            'image' => $blog->image,
            'featured' => $blog->featured,
            'category_id' => $blog->category_id,
            'created' => $blog->created,
            'modified' => $blog->modified,
            'jp' => true,
            'links' => [
                [
                    'url' => Router::fullBaseUrl() . '/api/blogs/' . $blog->id . '.json'
                ]
            ],
        ];
    }

}
```

The Transformer class can easily be created via the `cake bake` command:

```
$ bin\cake bake transformer Blog
```

Now set up your API by the following example:

```
<?php
namespace App\Controller\Api;

use Api\Controller\ApiControllerTrait;
use App\Controller\AppController;

/**
 * Blogs Controller
 *
 * @property \App\Model\Table\BlogsTable $Blogs
 */
class BlogsController extends AppController
{
    use ApiControllerTrait;

    public function initialize()
    {
        $this->loadComponent('Api.ApiBuilder', [
            'actions' => [
                'index' => 'Api.Index',
                'view' => 'Api.View',
                'add' => 'Api.Add',
                'edit' => 'Api.Edit',
            ]
        ]);

        parent::initialize();
    }

}
```