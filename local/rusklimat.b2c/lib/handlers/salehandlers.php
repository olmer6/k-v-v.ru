<?php
namespace Rusklimat\B2c\Handlers;

use Bitrix\Main;
use Bitrix\Main\Entity;

class SaleHandlers
{
	public static function Init()
	{
		/* Совмистимость со старым ядром */
		SaleHandlersCompatibility::Init();

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->addEventHandler('sale', 'OnSaleBasketItemBeforeSaved', array(__CLASS__, 'OnSaleBasketItemEntitySaved__OnSaleBasketItemBeforeSaved'));
	}

	/**
	 * @param Main\Event $event
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	public static function OnSaleBasketItemEntitySaved__OnSaleBasketItemBeforeSaved(\Bitrix\Main\Event $event)
	{
		/** @var $item \Bitrix\Sale\BasketItem */
		$item = $event->getParameter("ENTITY");

		if($item->isNew())
		{
			if(\Rusklimat\B2c\Helpers\Sale\Basket\Tools::getItemsCnt() >= \Rusklimat\B2c\Config\Sale::BASKET_MAX_SIZE)
			{
				$item->delete();

				$result = new \Bitrix\Main\EventResult(
					\Bitrix\Main\EventResult::ERROR,
					new \Bitrix\Sale\ResultError(
						'Превышен размер корзины',
						"SALE_EVENT_ON_BEFORE_BASKET_ITEM_SAVED_RK_MAX_SIZE"
					)
				);

				$event->addResult($result);
			}
		}
	}
}