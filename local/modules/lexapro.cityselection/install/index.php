<?
IncludeModuleLangFile(__FILE__);
if (class_exists('lexapro.cityselection'))
	return;

Class lexapro_cityselection extends CModule
{
	const MODULE_ID = 'lexapro.cityselection';
	var $MODULE_ID = 'lexapro.cityselection';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';
	var $errors = [];

	function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = getMessage('LEXAPRO_CITY_SELECTION_NAME');
		$this->MODULE_DESCRIPTION = GetMessage("LEXAPRO_CITY_SELECTION_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("LEXAPRO_CITY_SELECTION_PARTNER_NAME");
	}


	function doInstall()
	{
		$this->installDB();
	}

	function installDB()
	{
		GLOBAL $APPLICATION;

		$this->errors = false;

		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));
			return false;
		}

		registerModule($this->MODULE_ID);

		return true;
	}

	function doUninstall()
	{
		$this->uninstallDB();
	}

	function uninstallDB()
	{
		GLOBAL $APPLICATION;

		$this->errors = false;

		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));
			return false;
		}

		unregisterModule($this->MODULE_ID);

		return true;
	}
}