<?
namespace Rusklimat\B2c\Agents\Catalog;

/**
 * task https://portal.rusklimat.ru/company/personal/user/0/tasks/task/view/49418/
 */

use Rusklimat\B2c\Helpers\Main\Geo;

class MenuChecker
{
	public static function Execute()
	{
		self::run();

		sleep(5);

		self::run();

		return __CLASS__.'::'.__FUNCTION__.'();';
	}

	public function run()
	{
		$arCheckCityMenu = [
			[
				'ID' => 77669,
				'CODE' => 'Moscow'
			],
			[
				'ID' => 101990,
				'CODE' => 'Novosibirsk'
			],
			[
				'ID' => 102453,
				'CODE' => 'Omsk'
			],
		];

		$string = '';

		$string .= date("d.m.Y H:i:s").';	';

		foreach($arCheckCityMenu as $city)
		{
			$arGeo = [];
			$arGeo['CUR_CITY'] = Geo::getInstance()->getByID($city['ID']);

			$result = self::byGeo($arGeo);

			$string .= $arGeo['CUR_CITY']['NAME'].';	'.$result.';		';
		}

		$connection = \Bitrix\Main\Application::getConnection();

		$arCnt = $connection->query('SELECT
				COUNT(DISTINCT a.ID) CNT,
				COUNT(DISTINCT b.ID) PRICE,
				(
					SELECT
						COUNT(a2.ID) CNT
					FROM
						b_iblock_section a2
					WHERE
						a2.IBLOCK_ID = 8
						AND a2.ACTIVE = "Y"
						AND a2.GLOBAL_ACTIVE = "Y"
				) CNT_CATALOG,
				(
					SELECT
						COUNT(a3.ID) CNT
					FROM
						b_iblock_section a3
					WHERE
						a3.IBLOCK_ID = 17
						AND a3.ACTIVE = "Y"
						AND a3.GLOBAL_ACTIVE = "Y"
				) CNT_MENU,
				(
					SELECT
						COUNT(DISTINCT b4.IBLOCK_SECTION_ID)
					FROM
						a_catalog_products_availables a4
					LEFT JOIN
						b_iblock_element b4 ON b4.ID = a4.PRODUCT_ID AND b4.IBLOCK_ID = 8
					WHERE
						a4.CITY_ID = 77669
						AND a4.`STATUS` IN (1,2)
				) ACTIVE_SECTIONS
			FROM
				b_iblock_element a
			LEFT JOIN
				b_catalog_price b ON b.PRODUCT_ID = a.ID AND b.PRICE > 0
			WHERE
				a.IBLOCK_ID = 8
				AND a.ACTIVE = "Y"')->fetch();

		$string .= '	CNT:'.$arCnt['CNT'].';	PRICE:'.$arCnt['PRICE'].';	CNT_CATALOG:'.$arCnt['CNT_CATALOG'].';	CNT_MENU:'.$arCnt['CNT_MENU'].';	ACTIVE_SECTIONS:'.$arCnt['ACTIVE_SECTIONS'].';	';

		pre($string."\n");

		$tempFile = fopen($_SERVER["DOCUMENT_ROOT"].'/local/logs/agent.menuchecker.log', "a");
		fwrite($tempFile, $string."\n");
		fclose($tempFile);


		/*$data = [];

		$rsAvailables = $connection->query('SELECT
				c.ID,
				SUM((CASE WHEN b.ACTIVE = "Y" THEN 1 ELSE 0 END)) ACTIVE_Y,
				SUM((CASE WHEN b.ACTIVE = "N" THEN 1 ELSE 0 END)) ACTIVE_N
			FROM
				a_catalog_products_availables a
			LEFT JOIN
				b_iblock_element b ON b.IBLOCK_ID = 8 AND b.ID = a.PRODUCT_ID
			LEFT JOIN
				b_iblock_section c ON c.ID = b.IBLOCK_SECTION_ID	
			WHERE
				a.`STATUS` IN (1,2)	
				AND a.CITY_ID = 77669
			GROUP BY
				c.ID WITH ROLLUP');

		while($r = $rsAvailables->fetch())
		{
			if(empty($r['ID']))
				$r['ID'] = 'SUM';

			$data[] = implode('	',$r);
		}

		\Bitrix\Main\Diag\Debug::writeToFile($data, date("d.m.Y H:i:s"), '/local/logs/agent.menuchecker.availables_'.date("Y-m-d").'.log');*/

	}

	private function byGeo($arGeo = [])
	{
		GLOBAL $APPLICATION;

		$arrMenuFilter = [];

		if(!empty($arGeo['CUR_CITY']))
		{
			if (!empty($arGeo['CUR_CITY']['PRICE_ID']))
			{
				$arrMenuFilter['>CATALOG_PRICE_'.$arGeo['CUR_CITY']['PRICE_ID']] = '0';
			}
			else
			{
				$arrMenuFilter['<CATALOG_PRICE_1'] = '0';
			}

			ob_start();

			$APPLICATION->IncludeComponent(
				"bitrix:catalog.section.list",
				"menu.top.catalog",
				Array(
					"ADD_SECTIONS_CHAIN" => "N",
					"CACHE_GROUPS" => "N",
					"CACHE_TIME" => 3600,
					"CACHE_TYPE" => "N",
					"COMPONENT_TEMPLATE" => "menu.top.catalog",
					"COUNT_ELEMENTS" => "N",
					"IBLOCK_ID" => "17",
					"IBLOCK_TYPE" => "catalog",
					"SECTION_CODE" => "",
					"SECTION_FIELDS" => array("CODE","NAME","SORT","PICTURE",""),
					"SECTION_ID" => "",
					"SECTION_URL" => "",
					"SECTION_USER_FIELDS" => array("UF_REAL_CAT_LINK","UF_MENU_COL", "UF_STATIC_URL", "UF_MENU_NAME"),
					"SHOW_PARENT_NAME" => "Y",
					"TOP_DEPTH" => "4",
					"VIEW_MODE" => "LIST",
					"IBLOCK_ID_LINK" => "8",
					"FILTER" => $arrMenuFilter,
					"GEO_FOLDER" => $arGeo["CUR_CITY"]["CITY_FOLDER"],
					"GEO_CITY_WH_GENERAL" => $arGeo["CUR_CITY"]["CITY_WH_GENERAL"],
					"GEO_PREORDER_CHECK" => $arGeo["CUR_CITY"]["PREORDER_CHECK"],
					"GEO_PREPAY_CHECK" => $arGeo["CUR_CITY"]["PREPAY_CHECK"],
					"WH_HASH" => $arGeo["CUR_CITY"]["WH_HASH"],
					"SHOW_CNT" => $_REQUEST['SHOW_MENU_CNT'] == 'Y',
				)
			);

			$out = ob_get_contents();
			ob_end_clean();

			preg_match_all('/href(.+)<\//U', $out, $output_array);

			$file = '/local/logs/agent.menuchecker.links_'.date("Y-m-d").'.log';

			/*\Bitrix\Main\Diag\Debug::dumpToFile(date("d.m.Y H:i:s").'-----------------------------------------------------', "", $file);
			\Bitrix\Main\Diag\Debug::dumpToFile(count($output_array[0]), "CNT", $file);
			\Bitrix\Main\Diag\Debug::dumpToFile($output_array[0], "LINKS", $file);*/

			return count($output_array[0]);
		}
	}

}