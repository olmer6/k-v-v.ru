<?php
namespace Rusklimat\B2c\Handlers\Main;

/*
* Alex: Делаем кастомный тип поля для привязки к свойствам каталога
*/

class UserFilterProps extends \CUserTypeEnum
{
	public static function Init()
	{
		AddEventHandler('main', 'OnUserTypeBuildList', [__CLASS__, 'GetUserFilterTypeDescription']);
	}


	// инициализация пользовательского свойства для главного модуля
	function GetUserFilterTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "filterprops",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => "RK:Привязка к хар-кам каталога",
			"BASE_TYPE" => "enum",
		);
	}
	
	function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
 
        $result = '';
        $rsEnum = \CIBlockProperty::GetList(Array("NAME" => "ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>8, "PROPERTY_TYPE" => "S", "MULTIPLE" => "N"));
        if(!$rsEnum)
            return '';
 
        if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
        {
            $bWasSelect = false;
            $result2 = '';
            while($arEnum = $rsEnum->GetNext())
            {
                $bSelected = (
                    ($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $bWasSelect = $bWasSelect || $bSelected;
                $result2 .= '<label><input type="radio" value="'.$arEnum["ID"].'" name="'.$arHtmlControl["NAME"].'"'.($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.$arEnum["NAME"].'</label><br>';
            }
            if($arUserField["MANDATORY"]!="Y")
                $result .= '<label><input type="radio" value="" name="'.$arHtmlControl["NAME"].'"'.(!$bWasSelect? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</label><br>';
            $result .= $result2;
        }
        else
        {
            $bWasSelect = false;
            $result2 = '';
            while($arEnum = $rsEnum->GetNext())
            {
                $bSelected = (
                    ($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $bWasSelect = $bWasSelect || $bSelected;
                $result2 .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["NAME"].'</option>';
            }
 
            if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
            {
                $size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';
            }
            else
            {
                $arHtmlControl["VALIGN"] = "middle";
                $size = '';
            }
 
            $result = '<select name="'.$arHtmlControl["NAME"].'"'.$size.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
            if($arUserField["MANDATORY"]!="Y")
            {
                $result .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
            }
            $result .= $result2;
            $result .= '</select>';
        }
        return $result;
    }
 
    function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = array($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        elseif(!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array();
 
        $rsEnum = \CIBlockProperty::GetList(Array("NAME" => "ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>8, "PROPERTY_TYPE" => "S", "MULTIPLE" => "N"));
        if(!$rsEnum)
            return '';
 
        $result = '';
 
        if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
        {
            $result .= '<input type="hidden" value="" name="'.$arHtmlControl["NAME"].'">';
            $bWasSelect = false;
            while($arEnum = $rsEnum->GetNext())
            {
                $bSelected = (
                    (in_array($arEnum["ID"], $arHtmlControl["VALUE"])) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $bWasSelect = $bWasSelect || $bSelected;
                $result .= '<label><input type="checkbox" value="'.$arEnum["ID"].'" name="'.$arHtmlControl["NAME"].'"'.($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.$arEnum["NAME"].'</label><br>';
            }
        }
        else
        {
            $result = '<select multiple name="'.$arHtmlControl["NAME"].'" size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': ''). '>';
 
            $result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
            while($arEnum = $rsEnum->GetNext())
            {
                $bSelected = (
                    (in_array($arEnum["ID"], $arHtmlControl["VALUE"])) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $result .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["NAME"].'</option>';
            }
            $result .= '</select>';
        }
        return $result;
    }
 
    function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        $rsEnum = \CIBlockProperty::GetList(Array("NAME" => "ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>8, "PROPERTY_TYPE" => "S", "MULTIPLE" => "N"));
        if(!$rsEnum)
            return '';
 
        if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
            $size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';
        else
            $size = '';
 
        $result = '<select name="'.$arHtmlControl["NAME"].'"'.$size.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
        if($arUserField["MANDATORY"]!="Y")
        {
            $result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
        }
        while($arEnum = $rsEnum->GetNext())
        {
            $result .= '<option value="'.$arEnum["ID"].'"'.($arHtmlControl["VALUE"]==$arEnum["ID"]? ' selected': '').'>'.$arEnum["NAME"].'</option>';
        }
        $result .= '</select>';
        return $result;
    }
 
    function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        if(!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array();
 
        $rsEnum = \CIBlockProperty::GetList(Array("NAME" => "ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>8, "PROPERTY_TYPE" => "S", "MULTIPLE" => "N"));
        if(!$rsEnum)
            return '';
 
        $result = '<select multiple name="'.$arHtmlControl["NAME"].'" size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
        if($arUserField["MANDATORY"]!="Y")
        {
            $result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
        }
        while($arEnum = $rsEnum->GetNext())
        {
            $result .= '<option value="'.$arEnum["ID"].'"'.(in_array($arEnum["ID"], $arHtmlControl["VALUE"])? ' selected': '').'>'.$arEnum["NAME"].'</option>';
        }
        $result .= '</select>';
        return $result;
    }
}