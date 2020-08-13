<?
namespace Rusklimat\B2c\Helpers\Sale\Order;

class SendToCrm
{
	public $orderId = null;
	public $fields = [];
	public $properties = [];
	public $basket = [];
	public $discount = 0;
	public $arCurCity = [];
	public $user = [];

	public function __construct($orderId = 0)
	{
		if($orderId)
		{
			$this->orderId = $orderId;

			$this->getBasket();
			$this->getOrderFields();
			$this->getOrderProps();
			$this->getOrderProperties();
			$this->getOrderCurCity();
		}
	}

	private function getOrderFields()
	{
		$this->fields = \CSaleOrder::GetByID($this->orderId);
		$this->user = \CUser::GetByID($this->fields['USER_ID'])->Fetch();
	}

	private function getOrderProps()
	{
		$this->fields['PROPS'] = [];

		$rsProps = \CSaleOrderPropsValue::GetOrderProps($this->orderId);

		while ($arProp = $rsProps->Fetch())
		{
			$this->fields['PROPS'][$arProp['CODE']] = $arProp;
		}
	}

	private function getBasket()
	{
		$dbBasketItems = \CSaleBasket::GetList(
			[
				"NAME" => "ASC",
				"ID" => "ASC"
			],
			[
				"ORDER_ID" => $this->orderId
			],
			false,
			false,
			[]
		);

		while ($arItems = $dbBasketItems->Fetch())
		{
			$arItems["PRODUCT_BITRIX_ID"] = $arItems["PRODUCT_ID"];

			$product = \CIBlockElement::GetList(
				["ID", "PROPERTY_NS_CODE"],
				["ID" => $arItems["PRODUCT_ID"]],
				false,
				false,
				["ID", "PROPERTY_NS_CODE"]
			)->GetNext();

			if ($product)
			{
				$this->discount += $arItems['DISCOUNT_PRICE'] * $arItems["QUANTITY"];

				$arItems["PRODUCT_ID"] = $product["PROPERTY_NS_CODE_VALUE"];
			}
			else
			{
				unset($arItems["PRODUCT_ID"]);
			}

			$arItems['PROPS_VALUES'] = [];

			$rsProps = \CSaleBasket::GetPropsList(
				[],
				['BASKET_ID' => $arItems['ID'], 'CODE' => 'ЭтоПодарок']
			);

			while($prop = $rsProps->Fetch())
			{
				$arItems['PROPS_VALUES'][$prop['CODE']] = $prop['VALUE'];
			}

			$arBasketItems[] = $arItems;
		}

		$this->basket = $arBasketItems;
	}

	private function getOrderProperties()
	{
		$this->properties['PRICE_DELIVERY'] = $this->fields['PROPS']['HIDE_DELIVERY_PRICE']['VALUE'];
		$this->properties['USER_DESCRIPTION'] = $this->fields['USER_DESCRIPTION'];
		$this->properties['PRICE'] = $this->fields['PRICE'];
		$this->properties['DATE_INSERT'] = $this->fields['DATE_INSERT'];

		if(!empty($this->fields['PROPS']))
		{
			foreach($this->fields['PROPS'] as $prop)
			{
				if($prop['TYPE'] == 'FILE')
				{
					$this->properties['ORDER_PROP'][$prop['ORDER_PROPS_ID']][0]['SRC'] = \CFile::GetPath($prop['VALUE']);
				}
				else
				{
					$this->properties['ORDER_PROP'][$prop['ORDER_PROPS_ID']] = $prop['VALUE'];
				}
			}
		}
	}

	private function getOrderCurCity()
	{
		if($this->fields['PROPS']['HIDE_CURRENT_REGION']['VALUE'] && $this->fields['PROPS']['HIDE_CURRENT_CITY']['VALUE'])
		{
			$section = \Bitrix\Iblock\SectionTable::getList([
					'filter' => ['IBLOCK_ID' => 15, '=NAME' => $this->fields['PROPS']['HIDE_CURRENT_REGION']['VALUE']],
					'select' => ['ID', 'NAME']]
			)->fetch();

			if($section)
			{
				$city = \Bitrix\Iblock\ElementTable::getList([
						'filter' => ['IBLOCK_ID' => 15, 'IBLOCK_SECTION_ID' => $section['ID'], '=NAME' => $this->fields['PROPS']['HIDE_CURRENT_CITY']['VALUE']],
						'select' => ['ID', 'NAME']]
				)->fetch();

				if($city)
				{
					$this->arCurCity = \Rusklimat\B2c\Helpers\Main\Geo::getInstance()->getByID($city['ID']);
				}
			}
		}
	}

	public function getSellingOrganization()
	{
		static $cache;

		$result = [];

		if($this->arCurCity)
		{
			if(isset($cache[$this->arCurCity['ID']]))
			{
				$result = $cache[$this->arCurCity['ID']];
			}
			else
			{
				$props = \CIBlockElement::GetProperty(
					15,
					$this->arCurCity['ID'],
					"sort",
					"asc",
					[]
				);

				while ($prop = $props->Fetch())
				{
					$result[] = $prop;
				}

				$cache[$this->arCurCity['ID']] = $result;
			}
		}

		return $result;
	}

	public function send()
	{
		//Отправка данных в банк
		$bankResponse = \OnlinePaidFirstStep($this->orderId, $this->arCurCity);

		//send data to CRM
		$soap = new \CRMGateway(
			$this->arCurCity,
			'10ed05aa-e8ce-45c6-a116-7eab2cc38220',
			$this->arCurCity['ID'],
			[
				"BITRIX_ID" => $this->orderId,
				"FIELDS" => $this->fields,
				"PROPERTIES" => $this->properties,
				"BASKET" => $this->basket,
				"USER" => $this->user,
				"BANK_RESPONSE" => $bankResponse
			],
			$this->getSellingOrganization()
		);

		return $soap->sendToCRM();
	}

	public function sendEmail()
	{
		//send email to ( msk managers || regions managers ) && client
		$SendMailHack = new \SendMailHack(
			$this->arCurCity,
			'10ed05aa-e8ce-45c6-a116-7eab2cc38220',
			$this->arCurCity['ID'],
			[
				"BITRIX_ID" => $this->orderId,
				"FIELDS" => $this->fields,
				"PROPERTIES" => $this->properties,
				"BASKET" => $this->basket,
				"USER" => $this->user,
			],
			$this->getSellingOrganization()
		);

		//отправим юзеру
		$clientData = $SendMailHack->createClientMailData();
		\CEvent::Send($clientData['eventType'], 's1', $clientData['sendData'], "N", '');

		//отправим админам
		$adminData = $SendMailHack->createAdminMailData();
		\CEvent::Send($adminData['eventType'], 's1', $adminData['sendData'], "N", '');
	}
}