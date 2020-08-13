<?php
namespace Rusklimat\B2c\Handlers\IBlock;

use \Rusklimat\B2c\Config;

/**
 * Class SectionNameDeclension
 * @package Rusklimat\B2c\Handlers\IBlock
 *
 * #11713 для псеворазделов заполняем склонения
 */
class SectionNameDeclension
{
	public static function Init()
	{
		AddEventHandler('iblock', 'OnAfterIBlockSectionUpdate', [__CLASS__, 'OnAfterIBlockSectionUpdate']);
	}

	public static function OnAfterIBlockSectionUpdate($arFields)
	{
		if($arFields['IBLOCK_ID'] == Config\Catalog::ID)
		{
			if($_REQUEST['is_pseudosection'] == 'Y')
			{
				self::set($arFields['ID']);
			}
		}
	}

	public static function set($id)
	{
		GLOBAL $USER_FIELD_MANAGER;

		$arSection = \CIBlockSection::GetList(
			[],
			['IBLOCK_ID' => Config\Catalog::ID, 'ID' => $id],
			false,
			array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'UF_NAME_IP_MN', 'UF_NAME_VP_ED', 'UF_NAME_RP_ED', 'UF_PSEUDO_SECTION')
		)->Fetch();

		if(!empty($arSection) && !empty($arSection['IBLOCK_SECTION_ID']))
		{
			$brand = '';

			if(!empty($arSection['UF_PSEUDO_SECTION']))
			{
				$arSection['UF_PSEUDO_SECTION'] = unserialize($arSection['UF_PSEUDO_SECTION']);

				if(!empty($arSection['UF_PSEUDO_SECTION']['rule']))
				{
					foreach($arSection['UF_PSEUDO_SECTION']['rule'] as $rule)
					{
						if($rule['controlId'] == 'CondIBProp:8:5056')
						{
							$brand = $rule['value'];
							break;
						}
					}
				}
			}

			$arParentSection = \CIBlockSection::GetList(
				[],
				['IBLOCK_ID' => Config\Catalog::ID, 'ID' => $arSection['IBLOCK_SECTION_ID']],
				false,
				array('ID', 'IBLOCK_ID',  'UF_NAME_IP_MN', 'UF_NAME_VP_ED', 'UF_NAME_RP_ED')
			)->Fetch();

			// Название в именительном падеже, множественное число
			if(empty($arSection['UF_NAME_IP_MN']) && !empty($arParentSection['UF_NAME_IP_MN']))
			{
				$USER_FIELD_MANAGER->Update(
					'IBLOCK_'.Config\Catalog::ID.'_SECTION',
					$id,
					['UF_NAME_IP_MN' => $arParentSection['UF_NAME_IP_MN'].(!empty($brand)?' '.$brand:'')]
				);
			}

			// Название в винительном падеже, единственное число
			if(empty($arSection['UF_NAME_VP_ED']) && !empty($arParentSection['UF_NAME_VP_ED']))
			{
				$USER_FIELD_MANAGER->Update(
					'IBLOCK_'.Config\Catalog::ID.'_SECTION',
					$id,
					['UF_NAME_VP_ED' => $arParentSection['UF_NAME_VP_ED'].(!empty($brand)?' '.$brand:'')]
				);
			}

			// Название в родительном падеже, единственное число
			if(empty($arSection['UF_NAME_RP_ED']) && !empty($arParentSection['UF_NAME_RP_ED']))
			{
				$USER_FIELD_MANAGER->Update(
					'IBLOCK_'.Config\Catalog::ID.'_SECTION',
					$id,
					['UF_NAME_RP_ED' => $arParentSection['UF_NAME_RP_ED'].(!empty($brand)?' '.$brand:'')]
				);
			}
		}
	}
}