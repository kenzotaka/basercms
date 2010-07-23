<?php
/* SVN FILE: $Id$ */
/**
 * ルーティング定義
 *
 * PHP versions 4 and 5
 *
 * BaserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2010, Catchup, Inc.
 *								9-5 nagao 3-chome, fukuoka-shi
 *								fukuoka, Japan 814-0123
 *
 * @copyright		Copyright 2008 - 2010, Catchup, Inc.
 * @link			http://basercms.net BaserCMS Project
 * @package			baser.config
 * @since			Baser v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
/**
 * vendors内の静的ファイルの読み込みの場合はスキップ
 */
if(Configure::read('Baser.Asset')) {
	return;
}

if (file_exists(CONFIGS.'database.php')) {
	$cn = ConnectionManager::getInstance();
}
if(!empty($cn->config->baser['driver'])) {
	$parameter = Configure::read('Baser.urlParam');
	$mobileOn = Configure::read('Mobile.on');
	$mobilePrefix = Configure::read('Mobile.prefix');
	$mobilePlugin = Configure::read('Mobile.plugin');
/**
 * トップページ
 */
	if(!$mobileOn) {
		Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'index'));
	}else {
		Router::connect('/'.$mobilePrefix.'/', array('prefix' => 'mobile','controller' => 'pages', 'action'=>'display', 'index'));
	}
/**
 * 管理画面トップページ
 */
	Router::connect('admin', array('admin'=>true, 'controller' => 'dashboard', 'action'=> 'index'));
/**
 * ページ機能拡張
 * cakephp の ページ機能を利用する際、/pages/xxx とURLである必要があるが
 * それを /xxx で呼び出す為のルーティング
 */
	/* 1.5.9以前との互換性の為残しておく */
	// .html付きのアクセスの場合、pagesコントローラーを呼び出す
	if(strpos($parameter, '.html') !== false) {
		if($mobileOn) {
			Router::connect('/'.$mobilePrefix.'/.*?\.html', array('prefix' => 'mobile','controller' => 'pages', 'action' => 'display','pages/'.$parameter));
		}else {
			Router::connect('.*?\.html', array('controller' => 'pages', 'action' => 'display','pages/'.$parameter));
		}
	}else{
		/* 1.5.10 以降 */
		$Page = ClassRegistry::init('Page');
		if($Page){
			if(preg_match('/\/$/is', $parameter)) {
				$_parameters = urldecode(array($parameter.'index'));
			}else{
				$_parameters = array(urldecode($parameter),urldecode($parameter).'/index');
			}
			foreach ($_parameters as $_parameter){
				if(!$mobileOn){
					$conditions = array('Page.status'=>true,'Page.url'=>'/'.$_parameter);
				}else{
					$conditions = array('Page.status'=>true,'Page.url'=>'/mobile/'.$_parameter);
				}
				if($Page->field('id',$conditions)){
					if(!$mobileOn){
						Router::connect('/'.$parameter, am(array('controller' => 'pages', 'action' => 'display'),split('/',$_parameter)));
					}else{
						Router::connect('/'.$mobilePrefix.'/'.$parameter, am(array('prefix' => 'mobile','controller' => 'pages', 'action' => 'display'),split('/',$_parameter)));
					}
					break;
				}
			}
		}
	}
/**
 * プラグイン名の書き換え
 * DBに登録したデータを元にURLのプラグイン名部分を書き換える。
 */
	$PluginContent = ClassRegistry::init('PluginContent');
	if($PluginContent) {
		$PluginContent->addRoute($parameter);
	}
/**
 * 携帯ルーティング
 */
	if($mobileOn) {
		// プラグイン
		if($mobilePlugin) {
			// ノーマル
			Router::connect('/'.$mobilePrefix.'/:plugin/:controller/:action/*', array('prefix' => 'mobile'));
			// プラグイン名省略
			Router::connect('/'.$mobilePrefix.'/:plugin/:action/*', array('prefix' => 'mobile'));
		}
		// 携帯ノーマル
		Router::connect('/'.$mobilePrefix.'/:controller/:action/*', array('prefix' => 'mobile'));
	}
/**
 * ユニットテスト
 */
	Router::connect('/tests', array('controller' => 'tests', 'action' => 'index'));
/**
 * フィード出力
 * 拡張子rssの場合は、rssディレクトリ内のビューを利用する
 */
	Router::parseExtensions('rss');
}
else {
	Router::connect('/', array('controller' => 'installations', 'action' => 'index'));
}

Router::connect('/install', array('controller' => 'installations', 'action' => 'index'));
?>