<?php
namespace Rusklimat\B2c\Handlers\IBlock;

use \Rusklimat\B2c\Config;



/*
 * Пояснения:
 * (*)  - Мы принимаем массив array('VALUE' => , 'DESCRIPTION' => ) и должны его же вернуть. Если поле с описанием - оно будет содержаться в соответствующем ключе.
 */

class SectionUserProp
{
	public function Init()
	{
		AddEventHandler('iblock', 'OnIBlockPropertyBuildList', [__CLASS__, 'GetUserTypeDescription']);
	}
	
    /**
     * Возвращает описание типа свойства.
     * @return array
     */
    public function GetUserTypeDescription()
    {
        return array(
			"PROPERTY_TYPE" => 'G', 
			"USER_TYPE" => 'RkIblockSection',
			"DESCRIPTION" => "Привязка к разделам (без псевдоразделов)",
			"GetPropertyFieldHtml" => array(__CLASS__,'GetPropertyFieldHtml'),
			"GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
			
		);
    }

	
	
	
	
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{

		$bWasSelect = false;
		$options = SectionUserProp::GetOptionsHtml($arProperty, array($value["VALUE"]), $bWasSelect);

		$html = '<select name="'.$strHTMLControlName["VALUE"].'">';
		if($arProperty["IS_REQUIRED"] != "Y")
			$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>Выбрать</option>';
		$html .= $options;
		$html .= '</select>';
		return  $html;
	}

	public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
	{
		$max_n = 0;
		$values = array();
		if(is_array($value))
		{
			foreach($value as $property_value_id => $arValue)
			{
				if (is_array($arValue))
					$values[$property_value_id] = $arValue["VALUE"];
				else
					$values[$property_value_id] = $arValue;

				if(preg_match("/^n(\\d+)$/", $property_value_id, $match))
				{
					if($match[1] > $max_n)
						$max_n = intval($match[1]);
				}
			}
		}

		$bWasSelect = false;
		$options = SectionUserProp::GetOptionsHtml($arProperty, $values, $bWasSelect);

		$html = '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[]" value="">';
		$html .= '<select multiple name="'.$strHTMLControlName["VALUE"].'[]" size="15">';
		if($arProperty["IS_REQUIRED"] != "Y")
			$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>Выбрать</option>';
		$html .= $options;
		$html .= '</select>';
		
		return  $html;
	}
	
	public static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
	{
		$options = "";
		$bWasSelect = false;

		foreach(SectionUserProp::GetSections($arProperty["LINK_IBLOCK_ID"]) as $arItem)
		{
			$options .= '<option value="'.$arItem["ID"].'"';
			if(in_array($arItem["~ID"], $values))
			{
				$options .= ' selected';
				$bWasSelect = true;
			}
			$options .= '>'.str_repeat(" . ", $arItem["DEPTH_LEVEL"]-1).$arItem["NAME"].'</option>';
		}

		return  $options;
	}
	
	public static function GetSections($IBLOCK_ID)
	{
		static $cache = array();
		$IBLOCK_ID = intval($IBLOCK_ID);

		if(!array_key_exists($IBLOCK_ID, $cache))
		{
			$cache[$IBLOCK_ID] = array();
			if($IBLOCK_ID > 0)
			{
				$arSelect = array(
					"ID",
					"NAME",
					"DEPTH_LEVEL",
					"UF_PSEUDO_SECTION",
				);
				$arFilter = array (
					"IBLOCK_ID"=> $IBLOCK_ID,
					//"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
				);
				$arOrder = array(
					"LEFT_MARGIN" => "ASC",
				);
				$rsItems = \CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);
				while($arItem = $rsItems->GetNext())
				{
					$pseudo_section = unserialize(htmlspecialchars_decode($arItem['UF_PSEUDO_SECTION']));
					
					if($pseudo_section["is_pseudosection"] != "Y")
						$cache[$IBLOCK_ID][$arItem["ID"]] = $arItem;
				}
			}
		}
		return $cache[$IBLOCK_ID];
	}


}