# Data models

Data models are the wrapper classes to the JSON strings or php arrays (markup languages in the future). Models simplifies the manipulation and processing workflow for the JSON or array objects.

## Pros

- Avoid undefined index by design
- Dynamic access to the model properties so no need of mapping the class properties with JSON attributes
- IDE auto-completion using `@property` docblock
- Set has many and has one relationships between models
- Ability to assign default values for the attributes so the undefined attributes can be handled reliably
- Ability to add logic into the JSON data in the model
- Cast values to known types such as integer, string, float, boolean
- Cast values to Carbon object to work on date attributes easily
- Ability to implement custom cast types
- Manipulate the object and make it array or serialize to JSON back

## Install

    composer require cangelis/data-models

## Usage

Imagine you have a JSON data for a blog post looks like this

```
$data = '{
    "id": 1,
    "author": "Can Gelis",
    "created_at": "2019-05-11 22:00:00",
    "comments": [
        {
            "id": 1,
            "text": "Hello World!"
        },
        {
            "id": 2,
            "text": "What a wonderful world!"
        }
    ],
    "settings": {"comments_enable": 1}
}';
```

You can create the models looks like this

```php

use CanGelis\DataModels\JsonModel;
use CanGelis\DataModels\Cast\BooleanCast;
use CanGelis\DataModels\Cast\DateTimeCast;

/**
* Define docblock for ide auto-completion
*
* @property bool $comments_enable
*/
class Settings extends JsonModel {

    protected $casts = ['comments_enable' => BooleanCast::class];

    protected $defaults = ['comments_enable' => false];

}

/**
* Define docblock for ide auto-completion
*
* @property integer $id
* @property string $text
*/
class Comment extends JsonModel {}

/**
* Define docblock for ide auto-completion
*
* @property integer $id
* @property author $text
* @property Carbon\Carbon $created_at
* @property Settings $settings
* @property CanGelis\DataModels\DataCollection $comments
*/
class Post extends JsonModel {

    protected $defaults = ['text' => 'No Text'];

    protected $casts = ['created_at' => DateTimeCast::class];

    protected $hasMany = ['comments' => Comment::class];

    protected $hasOne = ['settings' => Settings::class];

}

```

Use the models

```php

$post = Post::fromString($data); // initialize from JSON String
$post = new Post(json_decode($data, true)); // or use arrays

$post->text // "No Text" in $defaults
$post->foo // returns null which doesn't have default value
$post->created_at // get Carbon object
$post->created_at->addDay(1) // Go to tomorrow
$post->created_at = Carbon::now() // update the creation time

$post->settings->comments_enable // returns true
$post->settings->comments_enable = false // manipulate the object
$post->settings->comments_enable // returns false
$post->settings->editable = false // introduce a new attribute

$post->comments->first() // returns the first comment
$post->comments[1] // get the second comment
foreach ($post->comments as $comment) {} // iterate on comments
$post->comments->add(['id' => 3, 'text' => 'Not too bad']) // add to the collection

$post->toArray() // see as array
$post->jsonSerialize() // serialize to json

/*
{"id":1,"author":"Can Gelis","created_at":"2019-11-14 16:09:32","comments":[{"id":1,"text":"Hello World!"},{"id":2,"text":"What a wonderful world!"},{"id":3,"text":"Not too bad"}],"settings":{"comments_enable":false,"editable":false}}
*/

```

## Custom Casts

If you prefer to implement more complex value casting logic, data models allow you to implement your custom ones.

Imagine you use Laravel Eloquent and want to cast an in a JSON attribute.

```php

// data = {"id": 1, "user": 1}

class EloquentUserCast extends AbstractCast {

    /**
     * The value is casted when it is accessed
     * So this is a good place to convert the value in the
     * JSON into what we'd like to see
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function cast($value)
    {
        if (!$value instanceof User) {
            return User::find($value);        
        }
        return $value;
    }

    /**
     * This method is called when the object is serialized back to
     * array or JSON
     * So this is good place to make the values
     * json compatible such as integer, string or bool
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function uncast($value)
    {
        if ($value instanceof User) {
            return $value->id;
        }
        return $value;
    }
}

class Post {
    
    protected $casts = ['user' => EloquentUserCast::class];
    
}

$post->user = User::find(2); // set the Eloquent model directly
$post->user = 2; // set only the id instead
$post->user // returns instance of User
$post->toArray()

['id' => 1, 'user' => 2]

```
