<?php
namespace Rusklimat\B2c\Handlers;

class IBlockHandlers
{
	public static function Init()
	{
		/* Совмистимость со старым ядром */
		IBlockHandlersCompatibility::Init();

		\Rusklimat\B2c\Handlers\IBlock\SectionNameDeclension::Init();
		\Rusklimat\B2c\Handlers\IBlock\SectionUserProp::Init();
	}
}