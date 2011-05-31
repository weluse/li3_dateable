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
	 * Wird benoetigt um ein Model an den Behavior zu binden.
	 *
	 * @param object $class
	 * @param array	$config
	 */
	public static function bind($class, array $config = array()) {
		
		$defaults = array(
			'autoIndex' => true,
			'updated' => array('field' => 'updated', 'format' => 'Y-m-d h:i:s', 'auto' => true),
			'created' => array('field' => 'created', 'format' => 'Y-m-d h:i:s', 'auto' => true)
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
		
		//Updated Filter setzen
		if($config['updated']['auto']) {
			$class::applyFilter('save', function($self, $params, $chain) use ($class) {
				$params = Dateable::invokeMethod('_formatUpdated', array(
					$class, $params
				));
				return $chain->next($self, $params, $chain);
			});
		}
		
		//Created filter setzen
		if($config['created']['auto']) {
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
	 * @todo implementieren
	 * @see li3_geo\extensions\data\behavior\Locatable
	 * @param object $class
	 * @param array $keys
	 * @param array $options
	 */
	public static function index($class, array $keys, array $options = array()) {
		
		return false; 
		
		$defaults = array('include' => array(), 'background' => true);
		$options += $defaults;

		$meta = $class::meta();
		$database = Connections::get($meta['connection']);

		list($updated, $created) = $keys;
		
		$base = 'updated';

		if (!$database || !$updated || !$created) {
			return false;
		}
	
		if (is_a($database, 'lithium\data\source\MongoDb')) {
			$index = array($base => '2d') + $options['include'];
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