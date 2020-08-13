<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

if(empty($_SERVER["DOCUMENT_ROOT"]))
	$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../../../..');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(Loader::includeModule('rusklimat.b2c'))
{
	Rusklimat\B2c\Agents\Catalog\MenuChecker::Execute();
}


