<?php
namespace Rusklimat\B2c\Handlers;

use Bitrix\Highloadblock as HL;

class MainHandlersCompatibility
{
	public static function Init()
	{
		AddEventHandler('main', 'OnPageStart', [__CLASS__, 'OnPageStart_301_redirect']);
		AddEventHandler('main', 'OnPageStart', [__CLASS__, 'OnPageStart_subDomainRedirectLocation']);
		AddEventHandler('main', 'OnPageStart', [__CLASS__, 'OnPageStart_clearBigCookie']);
		AddEventHandler('main', 'OnAdminTabControlBegin', [__CLASS__, 'OnAdminTabControlBeginUF']);
		AddEventHandler('main', 'OnProlog', [__CLASS__, 'OnPrologHandler']);
	}

	/**
	 * Редирект со старых поддоменов
	 */
	public static function OnPageStart_301_redirect()
	{
		$redirectList = [];
		
		global $arGeo;

		$obCache = new \CPHPCache();

		if ($obCache->InitCache(300, "redirectList", "/"))
		{
			$redirectList = $obCache->GetVars();
		}
		elseif ($obCache->StartDataCache())
		{
			$HlBlock = HL\HighloadBlockTable::getList(['filter' => ['=NAME' => 'Redirect301']])->fetch();

			if($HlBlock)
			{
				$entityDataClass = HL\HighloadBlockTable::compileEntity($HlBlock)->getDataClass();

				$getList = $entityDataClass::getList();

				while($row = $getList->fetch())
				{
					$redirectList[$row["UF_FROM"]] = $row["UF_TO"];
				}
			}
			
			$obCache->EndDataCache($redirectList);
		}
		
		if(!empty($redirectList))
		{
			
			$URL = $_SERVER['REDIRECT_URL'] ?? $_SERVER['SCRIPT_URL'];
			//Alex: если у нас не Москва, то нужно очистить город в урле для поиска
			// МО регион
			if($arGeo["CUR_CITY"]["REGION_ID"] != 21751 && $arGeo["CUR_CITY"]["REGION_ID"] != 21752)
			{
				$URL = str_replace($arGeo["CUR_CITY"]["CITY_FOLDER"]."/", "", $URL); // убираем "город/"

				if(!empty($redirectList[$URL]))
				{
					$newUrl = '/'.$arGeo["CUR_CITY"]["CITY_FOLDER"].$redirectList[$URL];

					// сам редирект
					if(!empty($newUrl))
						LocalRedirect($newUrl, false, "301 Moved permanently");
				}
			}
			else
			{
				if(!empty($redirectList[$URL]))
					LocalRedirect($redirectList[$URL], false, "301 Moved permanently");
			}
		}
	}
	
	/**
	 * Редирект со старых поддоменов
	 */
	public static function OnPageStart_subDomainRedirectLocation()
	{
		\Rusklimat\B2c\Helpers\Main\GeoRedirects::bySubDomain();
	}

	/**
	 * TODO Чистим большие куки, когда обновим битрикс, удалим
	 */
	public static function OnPageStart_clearBigCookie()
	{
		if(!empty($_COOKIE))
		{
			foreach($_COOKIE as $key => $row)
			{
				if(strlen($row) > 1000)
				{
					unset($_COOKIE[$key]);

					\Rusklimat\B2c\Helpers\Tools::setCookie($key, '', -1);
				}
			}
		}

		if(ToLower(substr($_SERVER['HTTP_USER_AGENT'],0, 9)) == 'wordpress')
		{
			#exit;
		}

	}

	/**
	 * @param $form
	 *
	 * Добавляем пользовательские свойства на странице заказа в админке
	 */
	public static function OnAdminTabControlBeginUF(&$form)
	{
		\Rusklimat\B2c\Helpers\Sale\Admin\Order\UFTabs::add($form);
	}
	
	/**
	 *
	 * После отработки пролога
	 */
	public static function OnPrologHandler()
	{
		if(isset($_SESSION["RK_BASKET"]))
			unset($_SESSION["RK_BASKET"]);
	}
}