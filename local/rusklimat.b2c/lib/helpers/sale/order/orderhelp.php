<?
namespace Rusklimat\B2c\Helpers\Sale\Order;

class OrderHelp
{
	// удаление старых файлов от заказов
	public static function deleteOldFiles()
	{
		$path = $_SERVER["DOCUMENT_ROOT"] . "/upload/sale/order/files/";
		
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle)))
			{
				if($file != "." && $file != "..")
				{
					if ((time()-filectime($path.$file)) > 3600)
					{
						unlink($path.$file);
					}
				}
			}
		}
	}
}