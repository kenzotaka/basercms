<?php
/**
 * BaserTestCase
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2015, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2015, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Lib.TestSuite.Fixture
 * @since			baserCMS v 3.1.0-dev
 * @license			http://basercms.net/license/index.html
 */

class BaserTestFixture extends CakeTestFixture {

/**
 * {@inheritDoc}
 */
	public function init() {
		if (empty($this->fields)) {
			$this->fields = $this->getSchema($this->name);
		}
		parent::init();
	}

/**
 * lib/Baser/Config/Schema以下のスキーマクラスからスキーマを取得
 *
 * @param string $name Model名
 * @throws RuntimeException
 * @return array
 */
	public function getSchema($name) {
		$tableName = Inflector::tableize($name);
		$plugins = array(null);
		$plugins = array_merge($plugins, Configure::read('BcApp.corePlugins'));

		$schemaFile = null;

		foreach ($plugins as $plugin) {
			$schemaFile = $this->findSchemaFile($tableName, $plugin);
			if ($schemaFile != null) {
				break;
			}
		}

		if ($schemaFile == null) {
			throw new RuntimeException('Schemaファイルが見つかりません');
		}

		require_once $schemaFile;

		$schemaClass = Inflector::camelize($tableName) . 'Schema';
		$schema = new $schemaClass();
		return $schema->tables[$tableName];
	}

/**
 * スキーマファイルを探す
 *
 * @param string $tableName テーブル名
 * @param string $plugin プラグイン名
 * @return null|string
 */
	public function findSchemaFile($tableName, $plugin = null) {
		if (empty($plugin)) {
			$configDir = BASER_CONFIGS;
		} else {
			$configDir = BASER_PLUGINS . Inflector::camelize($plugin) . DS . 'Config' . DS;
		}
		$schemaFile = $configDir . 'Schema' . DS . $tableName . '.php';

		if (file_exists($schemaFile)) {
			return $schemaFile;
		}
		return null;
	}

}