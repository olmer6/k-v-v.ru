<?
namespace Rusklimat\B2c\Helpers\Sale\Admin\Order;
/**
 * Class UFTabs
 * @package Rusklimat\B2c\Helpers\Sale\Admin\Order
 *
 * Добавляем пользовательские свойства на странице заказа в админке
 */
class UFTabs
{
	public static function add(&$form)
	{
		global $APPLICATION;

		if ($ORDER_ID = (int) $_REQUEST["ID"])
		{
			switch ($APPLICATION->GetCurPage())
			{
				case '/bitrix/admin/sale_order_edit.php':
					self::GetUFEditFormShowTab($form->tabs, $ORDER_ID, false);
					break;
				case '/bitrix/admin/sale_order_view.php':
					self::GetUFEditFormShowTab($form->tabs, $ORDER_ID, true);
					break;
			}
		}
	}

	private static function GetUFEditFormShowTab(&$arTabs, $ORDER_ID, $bReadOnly = false)
	{
		GLOBAL $USER_FIELD_MANAGER;

		/** @var /CUserTypeEntity $USER_FIELD_MANAGER */

		$bVarsFromForm = false;

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields('ORDER', $ORDER_ID, LANGUAGE_ID);

		$arUFGroups = [
			'UF_ORDER_1C_' => [],
			'UF_' => [],
		];

		if(!empty($arUserFields))
		{
			foreach ($arUserFields as $FIELD_NAME => $arUserField)
			{
				$arUserField["VALUE_ID"] = intval($ORDER_ID);

				foreach ($arUFGroups as $code => $items)
				{
					if (substr($FIELD_NAME, 0, strlen($code)) == $code)
					{
						$arUFGroups[$code][$FIELD_NAME] = $arUserField;
						continue 2;
					}
				}
			}
		}

		foreach ($arUFGroups as $code => $items)
		{
			if(!empty($items))
			{
				$arTab = self::GetUFEditFormTabArray($code);

				ob_start();

				foreach ($items as $FIELD_NAME => $arUserField)
				{
					echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);
				}

				$strTabContent = ob_get_clean();

				if ($bReadOnly)
				{
					$strTabContent = preg_replace("/\<(input|textarea|select)/is", "<$1 readonly disabled ", $strTabContent);
					$strTabContent = str_replace("new top.BX.file_input", "", $strTabContent);
					$strTabContent = str_replace("onclick", "onclick=\"return false;\" xonclick", $strTabContent);
				}

				$arTab["CONTENT"] = $strTabContent;

				$arTabs[] = $arTab;
			}
		}
	}

	private static function GetUFEditFormTabArray($type)
	{
		$lang = [
			'UF_ORDER_1C_' => [
				'TAB' => 'Обмен с CRM',
				'TITLE' => 'Обмен с CRM',
			],
			'UF_' => [
				'TAB' => 'Пользовательские свойства',
				'TITLE' => 'Пользовательские свойства',
			],
		];

		return array(
			"DIV" => "user_fields_tab_".ToLower($type),
			"TAB" => $lang[$type]?$lang[$type]['TAB']:$lang['UF_'],
			"TITLE" => $lang[$type]?$lang[$type]['TITLE']:$lang['UF_'],
		);
	}
}