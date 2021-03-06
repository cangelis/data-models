# Data models

[![Build Status](https://travis-ci.org/cangelis/data-models.svg?branch=master)](https://travis-ci.org/cangelis/data-models)

Data models is the beautiful way of working with structured data such as JSON, XML and php arrays. They are basically wrapper classes to the JSON and XML strings or php arrays. Models simplify the manipulation and processing workflow of the JSON, XML or php arrays.

## Pros

- Straightforward to get started (this page will tell you all the features)
- Avoid undefined index by design
- Dynamic access to the model properties so no need of mapping the class properties with JSON or XML attributes
- IDE auto-completion using `@property` docblock and make the API usage documented by default
- Has many and has one relationships between models
- Ability to assign default values for the attributes so the undefined attributes can be handled reliably
- Ability to add logic into the data in the model
- Cast values to known types such as integer, string, float, boolean
- Cast values to Carbon object to work on date attributes easily
- Ability to implement custom cast types
- Manipulate and work on the object models instead of arrays and make them array or serialize to JSON back

## Install

    composer require cangelis/data-models:^2.0

## JSON Usage

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
$post->comments->add(new Comment(['id' => 3, 'text' => 'Not too bad'])) // add to the collection

$post->toArray() // see as array
$post->toJson() // serialize to json

/*
{"id":1,"author":"Can Gelis","created_at":"2019-11-14 16:09:32","comments":[{"id":1,"text":"Hello World!"},{"id":2,"text":"What a wonderful world!"},{"id":3,"text":"Not too bad"}],"settings":{"comments_enable":false,"editable":false}}
*/

```

## XML Usage

It is pretty straightforward and very similar to JSON models.

Imagine an XML data:

```php
$data = '<Team Color="#ffffff">
    <Players>
        <Player><Name>Beckham</Name><BirthDate>1975-05-02</BirthDate></Player>
        <Player><Name>Zidane</Name><BirthDate>1972-06-23</BirthDate></Player>
    </Players>
    <TeamLocation>
       <City>Istanbul</City>
       <Country>Turkey</Country>     
    </TeamLocation>
</Team>';
```

You can setup a relationship looks like this:

```php
use CanGelis\DataModels\XmlModel;
use CanGelis\DataModels\Cast\DateCast;

class Player extends XmlModel {

    // root tag name <Player></Player>
    protected $root = 'Player';

    protected $casts = ['BirthDate' => DateCast::class];

}

class Address extends Xmlmodel {

    protected $root = 'Address';

}

class Team extends XmlModel {
    
    protected $root = 'Team';

    protected $hasMany = [
        'Players' => Player::class
    ];
    
    protected $hasOne = [
        'TeamLocation' => Address::class
    ];
    
    // the attributes in this array will be
    // behave as XML attributes see the example
    protected $attributes = ['Color'];

}
```

Once you setup the relationships and your data, you start using the data.

```php
$team = Team::fromString($data);

echo $team->TeamLocation->City; // returns Istanbul
$team->TeamLocation->City = 'Madrid'; // update the city

echo $team->Players->count(); // number of players
echo $team->Players[0]->Name; // gets first player's name

echo $team->Color; // gets the Color XML attribute
$team->Color = '#000000'; // update the XML Attribute

echo get_class($team->Players[0]->BirthDate); // returns Carbon\Carbon
$team->Players->add(Player::fromArray(['Name' => 'Ronaldinho'])); // add a new player

echo (string) $team; // make an xml string
```

The resulting XML will be;

```xml
<Team Color="#000000">
	<TeamLocation>
		<Country>Turkey</Country>
		<City>Madrid</City>
	</TeamLocation>
	<Players>
		<Player><Name>Beckham</Name><BirthDate>1975-05-02</BirthDate></Player>
		<Player><Name>Zidane</Name><BirthDate>1972-06-23</BirthDate></Player>
		<Player><Name>Ronaldinho</Name></Player>
	</Players>
</Team>
```


## Available Casts

Here are the available casts.

```php

    CanGelis\DataModels\Cast\BooleanCast
    CanGelis\DataModels\Cast\FloatCast
    CanGelis\DataModels\Cast\IntegerCast
    CanGelis\DataModels\Cast\StringCast
    // these require nesbot/carbon package to work
    CanGelis\DataModels\Cast\DateCast
    CanGelis\DataModels\Cast\DateTimeCast
    CanGelis\DataModels\Cast\Iso8601Cast
    
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
## Contribution

Feel free to contribute!