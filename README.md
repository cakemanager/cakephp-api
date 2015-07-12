# Api plugin for CakePHP

This plugin allows you to easily create flexible restful api's on the fly.

> Note: This is a non-stable plugin for CakePHP 3.0 at this time. It is currently under development and should be 
considered experimental. We are working hard for a first release!


## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Configurations](#configurations)
- [Keep in touch](#keep-in-touch)


## Installation
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require cakemanager/cakephp-api:dev-master
```

Now you need to load the plugin with:

```
$ bin/cake plugin load -b -r Api
```

Default the plugin-configurations can be done with `Configure::write()`. The Api-plugin has an 
[Settings-plugin](https://github.com/cakemanager/cakephp-settings) integration. By adding 
`Configure::write('Api.settings')` BEFORE loading the Api-plugin the settings will be saved in your database, and will 
be configurable in the [CakeAdmin Plugin](https://github.com/cakemanager/cakephp-cakeadmin) (or your own integration).


## Usage

### Response structure
The Api-plugin handles the following structure for responses:
```
{
    "url": "/api/blogs/1.json",
    "code": 200,
    "data": {
        "id": 1,
        "title": "My first blog",
        "body": "How are you?",
        "category_id": 1,
        "created_by": {
            "id": 1,
            "email": "bob@cakemanager.org"
        },
        "modified_by": {
            "id": 1,
            "email": "bob@cakemanager.com"
        },
        "created": "2015-06-27T08:39:02+0000",
        "modified": "2015-07-03T20:13:09+0000"
    }
}
```

Explanation:
- `url` - This is the requested url.
- `code` - This is the status-code. On failures `4xx` (client side) or `5xx` (server side) errors will be given.
- `data` - The requested data.

> Note: In a later version, you will be able to add your own structure. For now this will be the baseline.

### Basic introduction
Lets say we want to create an API for our blogs. First of all we need a controller which is prefixed with `Api`. Use the
shell to create this:
```
$ bin/cake bake controller --prefix Api Blogs
```

Now the `BlogsController` is created in `src/Controller/Api/BlogsController.php`. Remove all crud-actions so we will have
an empty controller.

Now add the following to let our api do the work:
```
use Api\Controller\ApiControllerTrait;

class BlogsController extends AppController
{
    use ApiControllerTrait;

    public function initialize()
    {
        parent::initialize();
        
        $this->loadComponent('Api.ApiBuilder', []);
    }

}
```
Explanation:
- The `use ApiControllerTrait` is added to invoke the requested action to fake it. When you request the action `view`
and it doesn't exists in your controller this trait is able to see that, and use our component for that.
- The `Api.ApiBuilder`-component handles all of the api-stuff. This component is also used to add configurations.

Now add the following to your `config/routes.php`:
```
Router::prefix('api', function ($routes) {
    $routes->extensions(['json']);
    $routes->resources('Blogs');
});
```

This will add the `BlogsController` to the rest-full routes of Cake. For more, read 
 http://book.cakephp.org/3.0/en/development/routing.html#creating-restful-routes.

From now on your api is working, but hold on, no actions are configured yet! Let's add some crud-actions:
```
$this->loadComponent('Api.ApiBuilder', [
    'actions' => [
        'index',
        'view',
        'add',
        'edit',
        'delete',
    ]
]);
```

Now all crud-actions will be added to your api.

### Enabling and disabling
The following examples can be used to enable or disable actions:
```
// enable single action
$this->ApiBuilder->enable('index');

// enable multiple actions
$this->ApiBuilder->enable(['index', 'view']);

// disable single action
$this->ApiBuilder->disable('index');

// disable multiple actions
$this->ApiBuilder->disable(['index', 'view']);
```

### Adding parent resources
In some cases you have parent resources like:
```
/api/blogs/:blog_id/comments
/api/blogs/:blog_id/comments/:id
```
> Reference: http://book.cakephp.org/3.0/en/development/routing.html#creating-nested-resource-routes

The Api-plugin has an integration for this parent resources. By calling the `index` action, the query will automatically
validate on the (in this case) `blog_id`. On save-queries the column will be automatically added. For that, use the 
following code in your controller:
```
// in the comments-controller
$this->ApiBuilder->addParentResource('Blogs', 'blog_id');
```

The first value is the name of the parent resource: `Blogs`. The second value is the column-name in the table, and also
the variable-name in the request (look at the first code-example above). In this case: `blog_id`.

### Customize actions
Of course you will be able customize your own actions. Look at this example:
```
public function view($id) {
    $this->set('data', $this->Blogs->get($id));

    return $this->ApiBuilder->execute('view');
}
```
This example shows you how you will be able to affect the `data` key of the response and let the ApiBuilder do its work.

### Custom actions
Adding your custom actions is easy:
```
public function active() {
    $this->set('data', $this->Blogs->find()->where(['Blogs.active' => true]));

    return $this->ApiBuilder->execute();
}
```
Using this action will return all active blogs on `api.org/api/blogs/active.json`.


## Configurations
The `ApiBuilderComponent` has the following configurations:
- `modelName` - The name of the model the controller uses. 
- `_serialize` - List of serialized variables. Serialized variables will be added to the json-response.
- `actions` - Actions which are implemented. Can also be changed by the `enable` and `disable` methods.

The `bootstrap.php` contains the following configurations:
- `Api.settings` - Bool if the settings-plugin should be used to store configurations. By adding this feature, settings
can be managed in the [CakeAdmin](https://github.com/cakemanager/cakephp-cakeadmin) panel.
- `Api.JWT` - Bool if JWT should be implemented in the API. JWT is implemented by the 
[JwtAuth Plugin](https://github.com/ADmad/cakephp-jwt-auth).


## Keep in touch
If you need some help or got ideas for this plugin, feel free to chat at [Gitter](https://gitter.im/cakemanager/cakephp-api).

Pull Requests are always more than welcome!