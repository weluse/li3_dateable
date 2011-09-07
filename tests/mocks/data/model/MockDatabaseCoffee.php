<?php
/**
 * li3_dateable: a lithium php behavior
 *
 * @copyright     Copyright 2011, weluse GmbH (http://weluse.de)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_dateable\tests\mocks\data\model;

class MockDatabaseCoffee extends \li3_behaviors\extensions\Model {

	protected $_actsAs = array("dateable" => array(
		'updated' => array('field' => 'updated', 'format' => 's', 'auto' => true),
		'created' => array('field' => 'created', 'format' => 's', 'auto' => true)
	));

	protected $_meta = array(
		'connection' => 'mock-database-connection'
	);

	protected $_schema = array(
		'id' => array('type' => 'integer'),
		'author_id' => array('type' => 'integer'),
		'title' => array('type' => 'string'),
	);

	public function getBehaviors(){
		return $this->_actsAs;
	}

	public static function getFilters(){
		return static::$_methodFilters;
	}

	//copied methods
	public static $connection = null;

	public static function resetSchema() {
		static::_object()->_schema = array();
	}

	public static function overrideSchema(array $schema = array()) {
		static::_object()->_schema = $schema;
	}

	public static function instances() {
		return array_keys(static::$_instances);
	}

	public static function &connection() {
		if (static::$connection) {
			return static::$connection;
		}
		return parent::connection();
	}
}

?>