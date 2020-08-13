<?
namespace Rusklimat;

use Bitrix\Main\Loader;
use Rusklimat\B2c\Handlers;

Loader::includeModule('iblock');
Loader::includeModule('highloadblock');
Loader::includeModule('catalog');
Loader::includeModule('sale');
Loader::includeModule('rusklimat.exchange');


include_once('tools/tools.php');

$arClasses = [];

\CModule::AddAutoloadClasses('rusklimat.b2c', $arClasses);

# load handlers
Handlers\MainHandlers::Init();
Handlers\SaleHandlers::Init();
Handlers\IblockHandlers::Init();

include_once "parts/vendor/autoload.php";