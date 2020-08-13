<?
namespace Rusklimat\B2c\Helpers\Catalog;

use Bitrix\Highloadblock as HL;

class Actions
{
	public static function getListHaveTimeToBuyByGeo($arGeo = [])
	{
		$result = [];

		if(!empty($arGeo["CUR_CITY"]["PRICEBUSH_ID"]))
		{
			$res_action = \CIBlockElement::GetList(
				[],
				[
					"IBLOCK_ID" => 53,
					"ACTIVE" => "Y",
					"=XML_ID" => $arGeo["CUR_CITY"]["PRICEBUSH_ID"],
					">DATE_ACTIVE_TO" => [false ,ConvertTimeStamp(false, "FULL" )]
				],
				false,
				false,
				["ID", "NAME", "PROPERTY_GOODS"]
			);

			while($obElement_action = $res_action->GetNextElement())
			{
				$arFields_action = $obElement_action->GetFields();

				$result[] = $arFields_action['PROPERTY_GOODS_VALUE'];
			}
		}

		return $result;
	}

	/**
	 * @param array $arGeo
	 * @param array $itemsXmlID
	 *
	 * @return array
	 *
	 * #24297  - Возвращает акции товаров для вывода в списке товаров
	 */
	public static function getByProductsXmlID($arGeo = [], $itemsXmlID = [])
	{
		$result = [];

		if(!empty($arGeo['CUR_CITY']['PRICEBUSH_ID']) && !empty($itemsXmlID))
		{
			if($arGeo['CUR_CITY']['PRICEBUSH_ID'] && !empty($itemsXmlID))
			{
				$actionsDisplacement = [];

				$HlBlock = HL\HighloadBlockTable::getList(['filter' => ['=NAME' => 'HlRkActionsDisplacement']])->fetch();

				if($HlBlock)
				{
					$entityDataClass = HL\HighloadBlockTable::compileEntity($HlBlock)->getDataClass();

					$getList = $entityDataClass::getList([
						'filter' => [
							'=UF_GOODS' => $itemsXmlID,
							'=UF_PRICEBUSH' => $arGeo['CUR_CITY']['PRICEBUSH_ID']
						]
					]);

					while($row = $getList->fetch())
					{
						if(!empty($row['UF_ACTION']))
							$actionsDisplacement[$row['UF_GOODS']][] = $row['UF_DISPLACEMENT'];
					}
				}

				$goodsExcept = [];

				$res = \CIBlockElement::GetList(
					["PROPERTY_GOODS_PAGE_SORT" => "ASC"],
					[
						"IBLOCK_ID" => 23,
						'!PROPERTY_PIC_PRODUCT' => false,
						'=PROPERTY_geo_networks' => $arGeo['CUR_CITY']['PRICEBUSH_ID'],
						"ACTIVE" => "Y",
						[
							"LOGIC" => "OR",
							['=PROPERTY_GOODS_XML' => $itemsXmlID],
							["PROPERTY_ALL_GOODS" => "Y"]
						],
						"ACTIVE_DATE" => "Y",
						">PROPERTY_GOODS_PAGE_SORT" => "0"
					],
					false,
					false,
					[
						'ID',
						'XML_ID',
						'NAME',
						'CODE',
						'PROPERTY_PIC_PRODUCT',
						'PROPERTY_GOODS_XML',
						'PROPERTY_GOODS_EXCEPT_XML',
						'PROPERTY_ALL_GOODS',
					]
				);

				while($arElement = $res->Fetch())
				{
					$arElement['DETAIL_PAGE_URL'] = '/special/'.$arElement['CODE'].'/';

					if($arElement['PROPERTY_ALL_GOODS_VALUE'] == 'Y')
					{
						foreach ($itemsXmlID as $itemCode)
						{
							if($itemCode == $arElement['PROPERTY_GOODS_EXCEPT_XML_VALUE'])
							{
								$goodsExcept[$itemCode][] = $arElement['ID'];
								unset($result[$itemCode][$arElement['ID']]);
							}
							elseif(empty($result[$itemCode][$arElement['ID']]) && !in_array($arElement['ID'], $goodsExcept[$itemCode]))
							{
								$result[$itemCode][$arElement['ID']] = $arElement;
							}
						}
					}
					elseif($arElement['PROPERTY_GOODS_XML_VALUE'] && !in_array($arElement['ID'], $goodsExcept[$arElement['PROPERTY_GOODS_XML_VALUE']]))
					{
						if($arElement['PROPERTY_GOODS_XML_VALUE'] == $arElement['PROPERTY_GOODS_EXCEPT_XML_VALUE'])
						{
							$goodsExcept[$arElement['PROPERTY_GOODS_XML_VALUE']][] = $arElement['ID'];
							unset($result[$arElement['PROPERTY_GOODS_XML_VALUE']][$arElement['ID']]);
						}
						elseif(empty($result[$arElement['PROPERTY_GOODS_XML_VALUE']][$arElement['ID']]))
						{
							$result[$arElement['PROPERTY_GOODS_XML_VALUE']][$arElement['ID']] = $arElement;
						}
					}
				}
			}
		}

		if(!empty($actionsDisplacement) && !empty($result))
		{
			foreach($result as $itemId => &$arActions)
			{
				if(!empty($actionsDisplacement[$itemId]))
				{
					foreach($arActions as $actionID => $actionData)
					{
						if(in_array($actionData['XML_ID'], $actionsDisplacement[$itemId]))
						{
							unset($arActions[$actionID]);
						}
					}
				}
			}
		}

		return $result;
	}
}