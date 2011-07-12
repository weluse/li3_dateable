<?php
/**
 * li3_dateable: a lithium php behavior
 *
 * @copyright     Copyright 2011, weluse GmbH (http://weluse.de)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_dateable\extensions\data\behavior;

use lithium\data\Connections;

/**
 * 
 */
class Dateable extends \lithium\core\StaticObject {

	/**
	 * An array of configurations indexed by model class name, for each model to which this class
	 * is bound.
	 *
	 * @var array
	 */
	protected static $_configurations = array();

	/**
	 * Beahvior init setup
	 *
	 * @param object $class
	 * @param array	$config
	 */
	public static function bind($class, array $config = array()) {

		$defaults = array(
			'autoIndex' => true,
			'updated' => array('field' => 'updated', 'format' => \DateTime::ISO8601, 'auto' => true),
			'created' => array('field' => 'created', 'format' => \DateTime::ISO8601, 'auto' => true)
		);
		$config += $defaults;

		$updated = $config['updated'];
		$created = $config['created'];

		//Index for MongoDB
		if ($config['autoIndex']) {
			static::index(
				$class,
				array($config['updated']['field'], $config['created']['field']),
				array()
			);
		}

		//set updated filter
		if ($config['updated']['auto']) {
			$class::applyFilter('save', function($self, $params, $chain) use ($class) {
				$params = Dateable::invokeMethod('_formatUpdated', array(
					$class, $params
				));
				return $chain->next($self, $params, $chain);
			});
		}

		//set created filter
		if ($config['created']['auto']) {
			$class::applyFilter('create', function($self, $params, $chain) use ($class) {
				$params = Dateable::invokeMethod('_formatCreated', array(
					$class, $params
				));
				return $chain->next($self, $params, $chain);
			});
		}

		return static::$_configurations[$class] = $config;
	}

	/**
	 * AutoIndex for MongoDB
	 *
	 * @todo not yet finished
	 * @see li3_geo\extensions\data\behavior\Locatable
	 * @param object $class
	 * @param array $keys
	 * @param array $options
	 */
	public static function index($class, array $keys, array $options = array()) {
			
		$defaults = array('include' => array(), 'background' => true);
		$options += $defaults;

		$meta = $class::meta();
		$database = Connections::get($meta['connection']);

		list($updated, $created) = $keys;
		
		$updated = is_string($updated) ? array($updated => 1) : $updated;
		$created = is_string($created) ? array($created => 1) : $created;
		
		if (!$database || !$updated || !$created) {
			return false;
		}
	
		if (is_a($database, 'lithium\data\source\MongoDb')) {
			$index = array('name' => 'li3_dateable') + $options['include'] + $updated + $created;
			$collection = $meta['source'];
			unset($options['include']);
			$database->connection->{$collection}->ensureIndex($index, $options);
		}
	}

	/**
	 * Formatiert die Datenstruktur für den Update
	 *
	 * @param string|object $class
	 * @param array $options
	 */
	protected static function _formatUpdated($class, $options) {
		$config = static::$_configurations[$class];
		$config = $config['updated'];

		$entity = $options['entity'];
		
		//only if Entity exists
		if($entity->exists()) {
			$datetime = date($config['format']);
			$options['data'][$config['field']] = $datetime;
		}

		return $options;
	}

	/**
	 * Formatiert die Datenstruktur für Created
	 *
	 * @param string|object $class
	 * @param array $options
	 */
	protected static function _formatCreated($class, $options) {
		$staticConfig = static::$_configurations[$class];
		$config = $staticConfig['created'];
		$time = time();
		$datetime = date($config['format'],$time);
		$options['data'][$config['field']] = $datetime;
		$config = $staticConfig['updated'];
		$datetime = date($config['format'],$time);
		$options['data'][$config['field']] = $datetime;
		return $options;
	}


}

?>