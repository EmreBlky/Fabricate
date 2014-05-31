<?php
App::uses('FabricateConfig', 'Fabricate.Lib');
App::uses('FabricateContext', 'Fabricate.Lib');

/**
 * Fabricator for CakePHP model.
 * This is inspired RSpec fablicator.
 */
class Fabricate {
	private static $_instance = null;
	private $config;

	/**
	 * Return Fabricator instance
	 */
	private static function getInstance() {
		if(self::$_instance == null) {
			self::$_instance = new Fabricate();
		}
		return self::$_instance;
	}

	/**
	 * Override constructor.
	 */
	public function __construct() {
       $this->config = new FabricateConfig();
	}

	/**
	 * To override these settings
	 * @param $callback($config) can override $config(class of FabricateConfig) attributes 
	 */
	public static function config($callback) {
		$callback(self::getInstance()->config);
	}

	/**
	 * Create and Save fablicated model data to database.
	 * @param $modelName string Model Name.
	 * @param $recordCount integer count for creating.
	 * @param $callback  mixed callback or array can change fablicated data if you want to overwrite
	 */
	public static function create($modelName, $recordCount=1, $callback = null) {
		$attributes = self::attributes_for($modelName, $recordCount, $callback);
		$model = ClassRegistry::init($modelName);
		foreach ($attributes as $data) {
			$model->create($data);
			$model->save(null, self::getInstance()->config->auto_validate);
		}
	}
	/**
	 * Only create a model instance.
	 * @param $modelName string Model Name.
	 * @param $callback  function callback can chenge fablicated data if you want to overwrite
	 * @return Model Initializes the model for writing a new record
	 */
	public static function build($modelName, $callback = null) {
		$data = self::attributes_for($modelName, 1, $callback);
		$model = ClassRegistry::init($modelName);
		$model->create($data[0]);
		return $model;
	}
	/**
	 * Only create model attributes array.
	 * @return array model attributes array.
	 */
	public static function attributes_for($modelName, $recordCount=1, $callback = null) {
		if(is_callable($recordCount) || is_array($recordCount)) {
			$callback = $recordCount;
			$recordCount = 1;
		}
		$model = ClassRegistry::init($modelName);
		$results = self::getInstance()->_generateRecords($model->schema(), $recordCount, $callback);
		return $results;
	}

	/**
	 * Generate String representation of Records
	 *
	 * @param array $tableInfo Table schema array
	 * @param integer $recordCount
	 * @return array Array of records.
	 */
	private function _generateRecords($tableInfo, $recordCount = 1, $callback) {
		$world = new FabricateContext($this->config);
		$records = array();
		for ($i = 0; $i < $recordCount; $i++) {
			$record = array();
			foreach ($tableInfo as $field => $fieldInfo) {
				if (empty($fieldInfo['type'])) {
					continue;
				}
				$insert = '';
				switch ($fieldInfo['type']) {
					case 'integer':
					case 'float':
						$insert = $this->config->sequence_start + $i;
						break;
					case 'string':
					case 'binary':
						$isPrimaryUuid = (
							isset($fieldInfo['key']) && strtolower($fieldInfo['key']) === 'primary' &&
							isset($fieldInfo['length']) && $fieldInfo['length'] == 36
						);
						if ($isPrimaryUuid) {
							$insert = String::uuid();
						} else {
							$insert = "Lorem ipsum dolor sit amet";
							if (!empty($fieldInfo['length'])) {
								$insert = substr($insert, 0, (int)$fieldInfo['length'] - 2);
							}
						}
						break;
					case 'timestamp':
						$insert = time();
						break;
					case 'datetime':
						$insert = date('Y-m-d H:i:s');
						break;
					case 'date':
						$insert = date('Y-m-d');
						break;
					case 'time':
						$insert = date('H:i:s');
						break;
					case 'boolean':
						$insert = 1;
						break;
					case 'text':
						$insert = "Lorem ipsum dolor sit amet, aliquet feugiat.";
						$insert .= " Convallis morbi fringilla gravida,";
						$insert .= " phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin";
						$insert .= " venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla";
						$insert .= " vestibulum massa neque ut et, id hendrerit sit,";
						$insert .= " feugiat in taciti enim proin nibh, tempor dignissim, rhoncus";
						$insert .= " duis vestibulum nunc mattis convallis.";
						break;
				}
				$record[$field] = $insert;
			}
			if(is_callable($callback)) {
				$record = array_merge($record, $callback($record, $world));
			} else if(is_array($callback)) {
				$record = array_merge($record, $callback);
			}
			$records[] = $record;
		}
		return $records;
	}


}