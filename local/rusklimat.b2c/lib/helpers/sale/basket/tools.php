<?
namespace Rusklimat\B2c\Helpers\Sale\Basket;

use \Bitrix\Main\Context;
use \Bitrix\Sale;
use \Bitrix\Sale\Basket;
use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Iblock\ElementTable;
use \Rusklimat\B2c\Config;

class Tools
{
	/**
	 * @return int
	 *
	 * Возвращает количество позиций товаров в корзине
	 */
	public static function getItemsCnt()
	{
		$basket = Basket::loadItemsForFUser(sale\Fuser::getId(), Context::getCurrent()->getSite());

		return $basket->count();
	}
}