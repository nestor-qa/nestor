<?php namespace Nestor\Model;

use \Config;

class Nestor {

	var $pluginManager;

	public function __construct() 
	{
		$this->pluginManager = \App::make('PluginManager');
	}

	public function getVersion()
	{
		return '0.7';
	}

	public function getAvailableThemes()
	{
		$themes = array();
		$theme_config = Config::get('theme::config');
		$theme_path = $theme_config['themeDir'];
		$path = app('path.public').'/'.$theme_path.'/';
		if ($handle = opendir($path))
		{
			while (false !== ($entry = readdir($handle))) {
				if (is_dir($path . $entry) && '.' !== $entry && '..' !== $entry) {
					$themes[] = $entry;
				}
			}
		}
		return $themes;
	}

	public function getPluginManager() 
	{
		return $this->pluginManager;
	}

}
