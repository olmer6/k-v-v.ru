<?php
namespace Rusklimat\B2c\Handlers\Main;

/**
 * Class HandlerMainFixResizeNoise
 *
 * Используем imagick для ресайза. Необходимо для решения проблемы при
 * ресайзе прозрачных png, когда на фоне появляются едва заметные шумы
 */
class ResizeImage
{
	public static function Init()
	{
		AddEventHandler('main', 'OnBeforeResizeImage', [__CLASS__, 'OnBeforeResizeImage']);
		AddEventHandler('main', 'OnAfterResizeImage', [__CLASS__, 'OnAfterResizeImage']);
	}

	static $lastResizeResultSrc = null;

	/**
	 * @param $file
	 * @param $param
	 * @param $callbackData
	 * @param $bNeedResize
	 * @param $sourceImageFile
	 * @param $cacheImageFileTmp
	 *
	 * @throws \ImagickException
	 */
	function OnBeforeResizeImage($file, $param, &$callbackData, &$bNeedResize, &$sourceImageFile, &$cacheImageFileTmp)
	{
		if(class_exists('imagick') && in_array($param[1], [0, 1, 2]))
		{
			self::$lastResizeResultSrc = null;

			$dir = pathinfo($cacheImageFileTmp)['dirname'];

			if(!\Bitrix\Main\IO\Directory::isDirectoryExists($dir))
				\Bitrix\Main\IO\Directory::createDirectory($dir);

			if(\Bitrix\Main\IO\Directory::isDirectoryExists($dir))
			{
				$arSourceFileSizeTmp = \CFile::GetImageSize($sourceImageFile);

				if($arSourceFileSizeTmp)
				{
					$bNeedCreatePicture = false;

					$arSourceSize = ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];
					$arDestinationSize = ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];

					\CFile::ScaleImage($arSourceFileSizeTmp[0], $arSourceFileSizeTmp[1], $param[0], $param[1], $bNeedCreatePicture, $arSourceSize, $arDestinationSize);

					if($bNeedCreatePicture)
					{
						try {
							$image = new \Imagick($sourceImageFile);

							if($param[1] == 2)
							{
								$image->cropImage($arSourceSize['width'], $arSourceSize['height'], $arSourceSize['x'], $arSourceSize['y']);
							}

							$image->resizeImage($arDestinationSize['width'], $arDestinationSize['height'], \Imagick::FILTER_PARZEN, 0.7);

							$image->adaptiveSharpenImage(1, 100);
							$image->setImageCompressionQuality(95);
							$image->blurImage(0, 0);

							$image->writeImage($cacheImageFileTmp);

							$image->clear();
							$image->destroy();

							if(file_exists($cacheImageFileTmp))
							{
								$bNeedResize = false;

								self::$lastResizeResultSrc = substr($cacheImageFileTmp, strlen($_SERVER["DOCUMENT_ROOT"]));
							}
						} catch (\ImagickException $e) {
							return;
						}
					}
				}
			}
		}
	}

	/**
	 * @param $file
	 * @param $param
	 * @param $callbackData
	 * @param $cacheImageFile
	 * @param $cacheImageFileTmp
	 * @param $arImageSize
	 *
	 * Иначе в 1 хит будет показана картинка без ресайза из-за флага $bNeedResize = false;
	 */
	function OnAfterResizeImage($file, $param, &$callbackData, &$cacheImageFile, &$cacheImageFileTmp, &$arImageSize)
	{
		if(self::$lastResizeResultSrc)
		{
			$cacheImageFile = self::$lastResizeResultSrc;

			self::$lastResizeResultSrc = null;
		}
	}
}