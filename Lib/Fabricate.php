<?php
App::uses('FabricateConfig', 'Fabricate.Lib');
App::uses('FabricateContext', 'Fabricate.Lib');
App::uses('FabricateRegistry', 'Fabricate.Lib');
App::uses('FabricateFactory', 'Fabricate.Lib/Factory');
App::uses('FabricateDefinition', 'Fabricate.Lib/Definition');

/**
 * Fabricator for CakePHP model.
 * This is inspired RSpec fablicator.
 */
class Fabricate {
	private static $_instance = null;
	private $config;
	private $registry;
	private $factory;

	/**
	 * Return Fabricator instance
	 */
	private static function getInstance() {
		if(self::$_instance == null) {
			self::$_instance = new Fabricate();
			self::$_instance->config = new FabricateConfig();
			self::$_instance->registry = new FabricateRegistry('Fabricate');
		}
		return self::$_instance;
	}

	/**
	 * Override constructor.
	 */
	public function __construct() {
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
		$instance = self::getInstance();
		$definition = $instance->definition($recordCount, $callback);
		$recordCount = $instance->recordCount($recordCount);
		return $instance->factory->create($attributes, $recordCount, $definition);
	}
	/**
	 * Only create a model instance.
	 * @param $modelName string Model Name.
	 * @param $callback  function callback can chenge fablicated data if you want to overwrite
	 * @return Model Initializes the model for writing a new record
	 */
	public static function build($modelName, $callback = null) {
		$data = self::attributes_for($modelName, 1, $callback);
		$instance = self::getInstance();
		$definition = $instance->definition(1, $callback);
		return $instance->factory->build($data, $definition);
	}
	/**
	 * Only create model attributes array.
	 * @return array model attributes array.
	 */
	public static function attributes_for($modelName, $recordCount=1, $callback = null) {
		$instance = self::getInstance();
		$instance->factory = $instance->factory($modelName);
		$definition = $instance->definition($recordCount, $callback);
		$recordCount = $instance->recordCount($recordCount);
		return $instance->factory->attributes_for($recordCount, $definition);
	}

	private function factory($name) {
		$factory = FabricateFactory::create(self::getInstance()->registry->find($name));
		$factory->setConfig(self::getInstance()->config);
		return $factory;
	}

	private function recordCount($recordCount) {
		if(is_callable($recordCount) || is_array($recordCount)) {
			$recordCount = 1;
		}
		return $recordCount;
	}

	private function definition($recordCount, $callback) {
		if(is_callable($recordCount) || is_array($recordCount)) {
			$callback = $recordCount;
		}
		return new FabricateDefinition($callback);
	}
}