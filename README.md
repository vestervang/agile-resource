# About

This is a **PoC**. The relationships will always use * to select from the DB and there is nothing preveting the n+1 problem. 

This package aims to make a single resource so you don't have to make one for each model.

# Features
* Backend to frontend mapping
* Result filtering
* Custom pagination method
* Order by from url parameter.
* Order direction
* Url building for relationships

# Installation

```
composer require vestervang/agile-resource
```

## Laravel >= 5.5.x

The package should be included in the auto-discovery process

## Laravel <= 5.4.x

__1__. Add the service provider to your ```config/app.php``` file 

```Vestervang\AgileResource\ServiceProvider::class```

# Usage

The resource support models that extends the ```Vestervang\AgileResource\Models\BaseModel``` class, generic objects (stdClass), arrays and scalar types

## Basic

### Models

```php
class User extends BaseModel
{
    protected $mapping = [
        [
            'backend' => 'name',
            'frontend' => 'Name',
        ],
        [
            'backend' => 'email',
            'frontend' => 'Email',
        ],
        [
            'backend' => 'posts',
            'frontend' => 'Posts',
            'routeName' => 'user.posts', // This is a reference to a named route
        ]
    ];
    
    ...
    
}
```

The only things required to get started in your models are a ```$mapping``` array and your model is extending the ```Vestervang\AgileResource\Models\BaseModel``` class.

### Using the resource

#### Fields
##### Let the user decide the response

```PHP
$user = User::find(1);
return new \Vestervang\AgileResource\Resources\Resource($user);
```
When using the resource like this the consumer of the api can deside what fields they want by specifying a fields attribute in the URL.

E.G.

We have a endpoint called ```/user/{id}```

When we are calling that endpoint we are getting a specific user and all fields for that user without relationships loaded.
```json
{
    "data": {
        "Name": "Greta McClure",
        "Email": "qlegros@example.com",
        "Posts": "http://resource.local/user/1/posts",
        "createdAt": "2019-05-28T16:49:44.000000Z",
        "updatedAt": "2019-05-28T16:49:44.000000Z"
    }
}
```

But if we call ```/user/{id}?fields=Name,Posts[Body]``` we get this response

```json
{
    "data": {
        "Name": "Greta McClure",
        "Posts": [
            {
                "Body": "Voluptatibus doloremque optio ..."
            }
        ]
    }
}
```

Now tell the endpoint to only get the ```Name``` and ```Posts``` fields. The ```[Body]``` after ```Posts``` means that we want to load the relationship and only return the Body field.

To get all fields in a relationship call ```/user/{id}?fields=Name,Posts[]```

##### Force specific response

Lets say we want the ```/user/{id}``` endpoint to always give the same response no matter what the consumer tell the api. We can do it like this

```PHP
$user = User::find(1);
return new \Vestervang\AgileResource\Resources\Resource($user, ['Name']);
```

The endpoint will now return the ```Name``` attribute no matter what the consumer tell the api.

#### Exclude

Lets say we want all fields except the ```Posts``` field. To do that we can set the exclude url parameter.

```/user/{id}?exclude=Posts```

Now we get this response

```json
{
    "data": {
        "Name": "Greta McClure",
        "Email": "qlegros@example.com",
        "createdAt": "2019-05-28T16:49:44.000000Z",
        "updatedAt": "2019-05-28T16:49:44.000000Z"
    }
}
```

__Important__: The fields array will always override the exclude array. So if both are set the exclude array is ignored.