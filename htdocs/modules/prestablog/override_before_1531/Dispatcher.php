<?php

class Dispatcher extends DispatcherCore
{
	protected function loadRoutes()
	{
		foreach(Module::getModulesDirOnDisk() As $module)
			if(Module::isInstalled($module)) {
				$content = file_get_contents(_PS_MODULE_DIR_.$module.'/'.$module.'.php');
				$pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>'.basename($module, '.php').'(Core)?)'
							.'(\s+extends\s+[a-z][a-z0-9_]*)?(\s+implements\s+[a-z][a-z0-9_]*(\s*,\s*[a-z][a-z0-9_]*)*)?\s*\{#i';
				if (preg_match($pattern, $content, $m)) {
					$ClassName = $m['classname'];
					require_once( _PS_MODULE_DIR_.$module.'/'.$module.'.php' );
					if(method_exists($ClassName,'getStaticModuleRoutes')) {
						$ModuleRoute = new $ClassName;
						foreach($ModuleRoute->getStaticModuleRoutes('ModuleRoutes') As $Routes)
							array_push($this->default_routes, $Routes);
					}
				}
			}
		parent::loadRoutes();
	}
}
