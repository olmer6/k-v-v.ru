<?php
namespace Rusklimat\B2c\Handlers;

class MainHandlers
{
	public static function Init()
	{
		/* Совмистимость со старым ядром */
		MainHandlersCompatibility::Init();

		\Rusklimat\B2c\Handlers\Main\ResizeImage::Init();
		\Rusklimat\B2c\Handlers\Main\UserFilterProps::Init();
		\Rusklimat\B2c\Handlers\Main\CookieUtmSource::Init();
	}
}