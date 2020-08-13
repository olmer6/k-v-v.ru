<?
namespace Rusklimat\B2c\Helpers\Sale\Basket;

use \Bitrix\Main\Context;
use \Bitrix\Sale;
use \Bitrix\Sale\Basket;
use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Iblock\ElementTable;
use \Rusklimat\B2c\Config;

class Add
{
	public $errors = [];

	private $fuserId = null;

	private  $id = 0;
	private  $quantity = 0;
	private  $fields = [];
	private  $props = [];

	/**
	 * Add constructor.
	 *
	 * @param int $id
	 * @param int $quantity
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function __construct($id = 0, $quantity = 1)
	{
		$this->id = (int) $id;
		$this->quantity = (int) $quantity;

		$this->fuserId = Sale\Fuser::getId();

		$this->getProductBasketData();
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getProductBasketData()
	{
		if($this->id)
		{
			$product = ElementTable::getList([
				'filter' => [
					'=IBLOCK_ID' => Config\Catalog::ID,
					'=ID' => $this->id,
					'=ACTIVE' => 'Y'
				],
				'select' => ['ID', 'CODE', 'NAME', 'ACTIVE', 'XML_ID'],
				'limit' => 1
			])->fetch();

			if($product)
			{
				$this->fields = [
					'PRODUCT_ID' => $product['ID'],
					'PRODUCT_XML_ID' => $product['XML_ID'],
					'NAME' => $product['NAME'],
					'QUANTITY' => $this->quantity,
					'PRODUCT_PROVIDER_CLASS' => 'RKCatalogProductProvider',
					'CURRENCY' => CurrencyManager::getBaseCurrency(),
					'LID' => Context::getCurrent()->getSite(),
				];

				$this->props = [
					[
						'NAME' => 'PRODUCT.XML_ID',
						'CODE' => 'PRODUCT.XML_ID',
						'VALUE' => $product['XML_ID'],
						'SORT' => 100,
					]
				];
			}
		}
	}

	/**
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\Result|\Bitrix\Main\Entity\UpdateResult|bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Exception
	 *
	 * Добавляем товар в корзину
	 */
	public function addToBasket()
	{
		if($this->fields['PRODUCT_ID'])
		{
			$basket = Basket::loadItemsForFUser($this->fuserId, Context::getCurrent()->getSite());

			/** @var \Bitrix\Sale\BasketItem|bool $item */
			if($item = $basket->getExistsItem('catalog', $this->fields['PRODUCT_ID'], ($this->props?$this->props:[])))
			{
				$item->setField('DELAY', 0);
				$item->setField('QUANTITY', $item->getQuantity() + $this->fields['QUANTITY']);

				if($this->fields['PRICE'])
					$item->setField('PRICE', $this->fields['PRICE']);
			}
			elseif($item = $basket->createItem('catalog', $this->fields['PRODUCT_ID']))
			{
				$item->setFields($this->fields);

				/** @var \Bitrix\Sale\BasketPropertiesCollection $property */
				$property = $item->getPropertyCollection();
				$property->setProperty($this->props);

				$itemSave = $item->save();

				if(!$itemSave->isSuccess())
				{
					return $itemSave;
				}
			}

			return $basket->save();
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 *  Как будет необходимо, добавим возможносьт добавления товара в заказ
	 */
	public function addToOrder()
	{
		return false;
	}
}