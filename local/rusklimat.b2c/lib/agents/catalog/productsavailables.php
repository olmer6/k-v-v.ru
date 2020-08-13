<?
namespace Rusklimat\B2c\Agents\Catalog;

use Rusklimat\B2c\Helpers\Main\Geo;
use Rusklimat\B2c\Helpers\Catalog\Product;
use Rusklimat\B2c\internals\Catalog\ProductsAvailablesTable;

/**
 * Class productsAvailables
 * @package Rusklimat\B2c\Agents\Catalog
 *
 * Заполняет таблицу со статусами товаров в разных городах
 */
class productsAvailables
{
	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function Execute()
	{
		$arCities = self::getCities();

		if(!empty($arCities))
		{
			$arCitiesWh = self::getCitiesWh($arCities);
			$arCitiesPrices = self::getCitiesPrices($arCities);

			if(!empty($arCitiesWh))
			{
				$catalogProductsAmounts = self::getProductStoreAmounts($arCitiesWh);
				$catalogProductsPrices = self::getProductPrices($arCitiesPrices);

				$rsElements = \CIBlockElement::GetList(
					[
						'ID' => 'ASC'
					],
					[
						'IBLOCK_ID' => 8,
						'ACTIVE' => 'Y',
//						[
//							'LOGIC' => 'OR',
//							['=PROPERTY_IMPORT_NSI_ACTIVE' => 1],
//							['=PROPERTY_IMPORT_NSI_ACTIVE' => false],
//						]
					],
					false,
					false,
					[
						'ID',
						'IBLOCK_SECTION_ID',
						'PROPERTY_PREORDER_CHECK',
						'PROPERTY_PREPAY_CHECK',
						'PROPERTY_IMPORT_NSI_ACTIVE'
					]
				);

				$arBlock = [];

				while($el = $rsElements->Fetch())
				{
					$arBlock[$el['ID']] = $el;

					if(count($arBlock) == 100)
					{
						self::updateBlock($arBlock, $arCities, $catalogProductsAmounts, $catalogProductsPrices);

						$arBlock = [];
					}
				}

				if(!empty($arBlock))
				{
					self::updateBlock($arBlock, $arCities, $catalogProductsAmounts, $catalogProductsPrices);
				}
			}
		}

		if(\Bitrix\Main\Loader::includeModule('iblock'))
		{
			\CIBlock::clearIblockTagCache(8);
		}

		return __CLASS__.'::'.__FUNCTION__.'();';
	}

	/**
	 * @param array $arBlock
	 * @param array $arCities
	 * @param array $catalogProductsAmounts
	 * @param array $catalogProductsPrices
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	private static function updateBlock($arBlock = [], $arCities = [], $catalogProductsAmounts = [], $catalogProductsPrices = [])
	{
		if(!empty($arBlock))
		{
			$connection = \Bitrix\Main\Application::getConnection();

			$lastData = self::getProductLastData(array_keys($arBlock));

			foreach($arBlock as $element)
			{
				$element['amount'] = $catalogProductsAmounts[$element['ID']];
				$element['prices'] = $catalogProductsPrices[$element['ID']];

				$element['to_insert'] = [];

				foreach($arCities as $city)
				{
					$element['key'] = $element['ID'].'_'.$city['ID'];

					if($element['PROPERTY_IMPORT_NSI_ACTIVE_VALUE'] == 0)
					{
						$element[$element['key']] = 0;
					}
					elseif(empty($element['prices'][$city['PRICE_ID']]))
					{
						$element[$element['key']] = 0;
					}
					elseif((int) $element['amount'][$city['CITY_WH_GENERAL']] > 0)
					{
						$element[$element['key']] = 1;
					}
					elseif($element['PROPERTY_IMPORT_NSI_ACTIVE_VALUE'] == 0)
					{
						$element[$element['key']] = 0;
					}
					else
					{
						$element['STATUS'][$city['ID']] = Product::getProductStatus(
							$element['ID'],
							[
								'PREORDER_CHECK' => ['VALUE' => $element['PROPERTY_PREORDER_CHECK_VALUE']],
								'PREPAY_CHECK' => ['VALUE' => $element['PROPERTY_PREPAY_CHECK_VALUE']],
							],
							$city['CITY_WH_GENERAL'],
							$city['PREORDER_CHECK'],
							$city['PREPAY_CHECK'],
							(int) $element['amount'][$city['CITY_WH_GENERAL']]
						);

						if($element['STATUS'][$city['ID']]['RK_STATUS'] == 'AVAILABLE')
						{
							$element[$element['key']] = 1;
						}
						elseif($element['STATUS'][$city['ID']]['RK_STATUS'] == 'PREORDER')
						{
							$element[$element['key']] = 2;
						}
						else
						{
							$element[$element['key']] = 0;
						}
					}

					if(isset($lastData[$element['key']]))
					{
						if($lastData[$element['key']][1] != $element[$element['key']])
						{
							ProductsAvailablesTable::update(
								$lastData[$element['key']][0],
								[
									'PRODUCT_ID' => $element['ID'],
									'CITY_ID' => $city['ID'],
									'STATUS' => $element[$element['key']],
								]
							);
						}

						unset($lastData[$element['key']]);
					}
					else
					{
						$element['to_insert'][] = '('.$element['ID'].','.$city['ID'].','.$element[$element['key']].')';
					}
				}

				if(!empty($element['to_insert']))
				{
					$connection->query('INSERT INTO a_catalog_products_availables (PRODUCT_ID, CITY_ID, STATUS) VALUES '.implode(',', $element['to_insert']).';');
				}
			}
		}
	}

	private static function getCities()
	{
		$result = [];

		$allLocations = Geo::getInstance()->getAllLocations();

		if(!empty($allLocations))
		{
			foreach($allLocations as $region)
			{
				foreach($region as $city)
				{
					$result[] = $city;
				}
			}
		}

		return $result;
	}

	private static function getCitiesWh($arCities = [])
	{
		$result = [];

		if(!empty($arCities))
		{
			foreach($arCities as $city)
			{
				if(!empty($city['CITY_WH_GENERAL']))
					$result[$city['CITY_WH_GENERAL']] = $city['CITY_WH_GENERAL'];
			}
		}

		return $result;
	}

	private static function getCitiesPrices($arCities = [])
	{
		$result = [];

		if(!empty($arCities))
		{
			foreach($arCities as $city)
			{
				if(!empty($city['PRICE_ID']))
					$result[$city['PRICE_ID']] = $city['PRICE_ID'];
			}
		}

		return $result;
	}

	private static function getProductStoreAmounts($arCitiesWh = [])
	{
		$result = [];

		if(!empty($arCitiesWh))
		{
			$rsStore = \Bitrix\Catalog\StoreProductTable::getList([
				'filter' => [
					'STORE_ID' => $arCitiesWh
				]
			]);

			while($store = $rsStore->fetch())
			{
				$result[$store['PRODUCT_ID']][$store['STORE_ID']] = $store['AMOUNT'];
			}
		}

		return $result;
	}

	private static function getProductPrices($arCitiesPrices = [])
	{
		$result = [];

		if(!empty($arCitiesPrices))
		{
			$rsStore = \Bitrix\Catalog\PriceTable::getList([
				'filter' => [
					'CATALOG_GROUP_ID' => $arCitiesPrices
				]
			]);

			while($store = $rsStore->fetch())
			{
				$result[$store['PRODUCT_ID']][$store['CATALOG_GROUP_ID']] = $store['PRICE'];
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function getProductLastData($ids = [])
	{
		$result = [];

		if(!empty($ids))
		{
			$rsDate = ProductsAvailablesTable::getList([
				'filter' => [
					'PRODUCT_ID' => $ids
				]
			]);

			while($row = $rsDate->fetch())
			{
				$result[$row['PRODUCT_ID'].'_'.$row['CITY_ID']] = [
					0 => $row['ID'],
					1 => $row['STATUS'],
				];
			}
		}

		return $result;
	}
}