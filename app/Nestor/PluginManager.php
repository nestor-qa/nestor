<?php namespace Nestor;

use \Config;

class PluginManager {

	var $pluginsFolder;

	public function __construct() 
	{
		$this->pluginsFolder = Config::get('nestor/plugins.plugins_folder');
	}

	

}
