#Dateable Behavior
This Behavior is using the li3_behaviors library of the flexible and most RAD development framework for PHP 5.3+ Lithium
This behavior will use MongoDate objects as the date types.

See:
- http://rad-dev.org/lithium/
- http://rad-dev.org/li3_behaviors/

This dateable behavior automatically adds two new attributes to your model: *created* and *updated* via Filters.
We've chosen *create* & *update* because of **CRUD** but are customizable.

##Installation

Checkout the code to either of your library directories:

    cd libraries
    git clone git@github.com

Include the library in in your `/app/config/bootstrap/libraries.php`

    Libraries::add('li3_dateable');

This Library is using the li3_behaviors library. It will load the li3_behaviors lib automatically, so activating the li3_behaviors in your bootstrap is optional.

##Usage

###Model
According to the li3_behaviors lib, your model must be a child class of:

`\li3_behaviors\extensions\Model`

Example:

	class Foo extends \li3_behaviors\extensions\Model {

		protected $_actsAs = array(
			'Dateable'
		);
	}

thats it :)

Now your Model is using the dateable behavior.

If you want to configure the behavior you should use the following:

	class Foo extends \li3_behaviors\extensions\Model {

		protected $_actsAs = array(
			'Dateable' => array(
				'updated' => array('field' => 'updated', 'index' => true),
				'created' => array('field' => 'created', 'index' => true),
			)
		);
	}

then you can edit the field names and deactivate the automatic creation of the indexes in mongodb


As mentioned above, this behavior is using the lithium Filter-Rules:

- `create` every time you create a new Entity via: `Model::create()`, the attributes `created` & `updated` will be filled with a MongoDate Object
- `update` on every update, only the attribute `update` will be filled with the MongoDate Object

=== Future ===
- Implementaton of diferent date formats like the original proyect instead of MongoDate
- Test Coverage
