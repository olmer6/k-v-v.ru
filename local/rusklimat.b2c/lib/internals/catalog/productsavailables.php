<?

namespace Rusklimat\B2c\internals\Catalog;

use Bitrix\Main;
use Bitrix\Main\Entity\{DataManager, IntegerField, StringField, DatetimeField};

/**
 * Class ProductsAvailablesTable
 * @package Rusklimat\B2c\internals\Catalog
 *
 * STATUS = 0 - не продается
 * STATUS = 1 - продается
 * STATUS = 2 - под заказ
 */
class ProductsAvailablesTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'a_catalog_products_availables';
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('ID',['primary' => true]),
			new IntegerField('PRODUCT_ID'),
			new IntegerField('CITY_ID'),
			new IntegerField('STATUS'),
			new StringField('DATE_UPDATE'),
			'ELEMENT' => array(
				'data_type' => 'Bitrix\Iblock\ElementTable',
				'reference' => array('=this.PRODUCT_ID' => 'ref.ID'),
			),
		);
	}
}
