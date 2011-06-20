<?php
/**
 * li3_dateable: a lithium php behavior
 *
 * @copyright     Copyright 2011, weluse GmbH (http://weluse.de)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_dateable\tests\cases\data\behavior;


use lithium\data\Connections;
//use lithium\data\model\Query;
//use lithium\data\entity\Record;
use lithium\tests\mocks\data\model\MockDatabase;

use li3_dateable\tests\mocks\data\model\MockDatabaseCoffee;

class DateableTest extends \lithium\test\Unit {

	protected $_configs = array();

	public function setUp() {
		$this->db = new MockDatabase();
		$this->_configs = Connections::config();

		Connections::reset();
		Connections::config(array('mock-database-connection' => array(
			'object' => &$this->db,
			'adapter' => 'MockDatabase'
		)));
	}

	public function tearDown() {
		Connections::reset();
		Connections::config($this->_configs);
	}

	public function testhasCreated(){
		$model = MockDatabaseCoffee::create(array('id'=>12));//,array("exists" => true)

		$data = $model->data();

		$this->assertTrue(isset($data['created']));
		$this->assertTrue(isset($data['updated']));
	}

	public function testUpdated() {
		$model = MockDatabaseCoffee::create(array('id'=>13));

		MockDatabaseCoffee::applyFilter('update', function($self, $params, $chain) {
			$val = $chain->next($self, $params, $chain);
			return $params['data'];
		});
		sleep(1);
		$newData = MockDatabaseCoffee::update(array('id'=>12));

		$oldData = $model->data();

		$changes = array_diff_assoc($newData, $model->data());

		$this->assertTrue(isset($changes['updated']));
	}
}

?>