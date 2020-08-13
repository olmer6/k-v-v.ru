<?
namespace Rusklimat\B2c\Helpers\Sale\Order;

use \Rusklimat\B2c\Config;
use \Bitrix\Sale;
use \Bitrix\Sale\Basket;
use \Bitrix\Sale\Payment;
use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Main\Context;

class CrmToSite
{
	public $orderId = null;
	public $gateOrders = null;
	public $packetId = null;
	
	protected $connection = array(); // соединение с шлюзом
	
	public function __construct($orderId = 0)
	{
		try {
			$this->connection = new \RusklimatConnect;
		} catch (Exception $e) {
			//$this->setError($e->GetMessage());
			echo "Не удалось установить соединение: " . $e->GetMessage();
			die();
		}		
		
		if($orderId > 0)
		{
			$gateRes = $this->connection->getRest("crm/order/exportZakaz/".$orderId, array(), "POST", true)["data"];
			$this->gateOrders[] = $gateRes["return"]["Заказ"];
		}
		else
		{
			$gateRes = $this->connection->getRest("exportZakaz", array(), "POST")["data"];
			$this->gateOrders = $gateRes["return"]["Заказ"];
			$this->packetId = $gateRes["return"]["НомерОтправленного"];
		}
	}
	
	/*
	 * 
	 */
	function updateOrder()
	{
		//$fake_id = 7598;
		if(empty($this->gateOrders))
		{
			echo "Пришел пустой массив из шлюза";
			die();
		}

		
		
		$arPriceBush = \RusklimatElement::getElements( array("IBLOCK_ID" => Config\Catalog::IBLOCK_BUSH), array("ID","NAME","XML_ID","ACTIVE","CODE"));
		$arCity = \RusklimatElement::getElements( array("IBLOCK_ID" => Config\Catalog::IBLOCK_GLOBUS), array("ID","NAME","XML_ID","ACTIVE","CODE"));
		$arCityID = \RusklimatElement::getElements( array("IBLOCK_ID" => Config\Catalog::IBLOCK_GLOBUS), array("ID","NAME","XML_ID","ACTIVE","CODE"), "ID");
		$arProducts = \RusklimatElement::getElements( array("IBLOCK_ID" => Config\Catalog::ID), array("ID","NAME","XML_ID","ACTIVE","CODE"), "XML_ID");
		
		// получаем айдишники свойств с привязкой к типу плательщика "PERSON_TYPE" => ["PROP_NAME" => "PROP_ID"]
		$arPropMatrix = $this->getPropMatrix();

		// все типы оплат
		$arPayments = $this->getPayments();
		// все типы доставок
		$arDeliveries = $this->getDeliveries();

		foreach($this->gateOrders as $gateOrder)
		{
			$personType = 0;
			// получаем заказ
			//$gateOrder["ЗаказИД"] = $fake_id; // для теста
			$order = Sale\Order::load($gateOrder["ЗаказИД"]);
			
			$personType = $order->getPersonTypeId();			

			// для работы со свойствами
			$propertyCollection = $order->getPropertyCollection();

			// ценовой куст
			$propPriceBush = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_CURRENT_NETWORK"]);

			if($arPriceBush[$gateOrder["ЦеновойКустИД"]]["ID"] == 57366) // в мск почему-т нет CODE
				$propPriceBush->setValue('Москва [moscow]');
			else
				$propPriceBush->setValue($arPriceBush[$gateOrder["ЦеновойКустИД"]]["NAME"].' ['.$arPriceBush[$gateOrder["ЦеновойКустИД"]]["CODE"].']');
			
			// географ глобус пропил
			$propGlobus = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_CURRENT_CITY"]);
			$propGlobus->setValue($arCity[$gateOrder["ГлобусИД"]]["NAME"]);
			
			// промокоды
			$propPromokod = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_BASKET_COUPON"]);
			$propPromokod->setValue($gateOrder["Промокод"]);
			// !!!!!!!!! по хорошему его бы еще к заказу применить, но пока оставим так
			
			// ОПЛАТЫ
			if(!empty($gateOrder["ТипПлатежа"]))
			{
				// тип оплаты из CRM
				$arPayment = $this->getCurrentPayment($gateOrder["ТипПлатежа"], $arPayments);
				// тип оплаты в заказе на сайте
				$paymentId = $order->getPaymentSystemId()[0]; // мы считаем, что у нас может быть только один тип оплаты !!!
				
				// если они не совпадают, и в заказе есть типы оплат
				if($arPayment["ID"] != $paymentId && !empty($paymentId))
				{
					$paymentCollection = $order->getPaymentCollection();
					
					// перебираем все оплаты и удаляем
					foreach ($paymentCollection as $payment) {
						$idPaymentOrder = $payment->getId(); // IDшник оплаты (не платежной сисетмы !)
						$paymentOld = $paymentCollection->getItemById($idPaymentOrder);
						$delResult = $paymentOld->delete();
					}
					
					// добавляем новый
					$payment = $paymentCollection->createItem();
					$payment->setFields(array(
						'PAY_SYSTEM_ID' => $arPayment["ID"],
						'PAY_SYSTEM_NAME' => $arPayment["NAME"],
					));
				}
				elseif($arPayment["ID"] != $paymentId)
				{
					$paymentCollection = $order->getPaymentCollection();
					// добавляем новый
					$payment = $paymentCollection->createItem();
					$payment->setFields(array(
						'PAY_SYSTEM_ID' => $arPayment["ID"],
						'PAY_SYSTEM_NAME' => $arPayment["NAME"],
					));
				}
			}
			// END: ОПЛАТЫ
			
			
			//СтатусНаСайте
			
			// ***** АДРЕС ДОСТАВКИ
			
			// Регион доставки
			$propRegion = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_CURRENT_REGION"]);
			$propRegion->setValue($gateOrder["СтруктураАдреса"]["Регион"]);
			// Город доставки
			$propCity = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_CURRENT_REGION"]);
			$propCity->setValue($arCityID[$gateOrder["СтруктураАдреса"]["Город"]]["NAME"]);
			// Улица доставки
			$propStreet = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_D_ADDR"]);
			$propStreet->setValue($gateOrder["СтруктураАдреса"]["Улица"]);
			// Дом доставки
			$propHouse = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_D_HOUSE"]);
			$propHouse->setValue($gateOrder["СтруктураАдреса"]["Дом"]);
			// Корпус доставки
			$propKorp = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_D_CORP"]);
			$propKorp->setValue($gateOrder["СтруктураАдреса"]["Корпус"]);
			// Строение доставки
			$propBuild = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_D_BUILD"]);
			$propBuild->setValue($gateOrder["СтруктураАдреса"]["Строение"]);
			// Квартира доставки
			$propRoom = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_D_ROOM"]);
			$propRoom->setValue($gateOrder["СтруктураАдреса"]["Квартира"]);
			
			// ***** END: АДРЕС ДОСТАВКИ
			
			$idStatusCRM = $this->getStatusID($gateOrder["СтатусНаСайте"]);
			$idStatusOrder = $order->getField("STATUS_ID");
			if($idStatusOrder != $idStatusCRM)
			{
				$order->setField("STATUS_ID", $idStatusCRM);
			}
			
			// для физиков
			if($personType == 1)
			{
				// Сказали не обновлять пока что
				// Банк
				//$propBankIdOnline = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["BANK_ORDER_ID"]);
				//$propBankIdOnline->setValue($gateOrder["СтруктураОнлайнОплаты"]["OrderId"]);
			}
			
			
			
			
			// Дата доставки
			$propDateDelivery = $propertyCollection->getItemByOrderPropertyId($arPropMatrix[$personType]["HIDE_DELIVERY_TIME"]);
			$explodeDateDelivery = explode("T", $gateOrder["ЖелаемаяДатаПолученияЗаказа"]);
			$propDateDelivery->setValue($explodeDateDelivery[0]);
			
			// Комментарий
			if(!empty($gateOrder["Комментарий"]))
				$order->setField("USER_DESCRIPTION", $gateOrder["Комментарий"]);

			
			//  ***** Разбиремся с корзиной + доставкой
	
			$basket = $order->getBasket();

			if(!empty($gateOrder["СоставЗаказа"]))
			{
				// мини костылек, чтобы не обрабатывать 2 раза, там где всего одна позиция
				if(empty($gateOrder["СоставЗаказа"][0]) && $gateOrder["СоставЗаказа"]["НоменклатураИД"])
					$gateOrder["СоставЗаказа"] = array($gateOrder["СоставЗаказа"]);
				
				$priceDeliveryCrm = 0;
				// на всякий случай проверим
				if(!empty($gateOrder["СоставЗаказа"][0]))
				{
					
					$arBasketToDelete = $this->getBasketToDelete($gateOrder["ЗаказИД"]);
					
					foreach($gateOrder["СоставЗаказа"] as $prod)
					{
						// для доставок
						if(
							$prod["НоменклатураИД"] == "988276e2-0828-4ec6-8c3d-fbb6b2a6ddaf" ||
							$prod["НоменклатураИД"] == "36eca6a6-883b-4068-9d1d-bd60818e4d30"
						)
						{
							$priceDeliveryCrm = $prod["ЦенаПрайс"];
						}
						else // товары
						{
							if($prod["ЭтоУслуга"] != 1)
							{
								// проверяем есть ли вообще такой товар в каталоге на сайте
								if(!empty($arProducts[$prod["НоменклатураИД"]]))
								{
									$fields = array(
										'PRODUCT_ID' => $arProducts[$prod["НоменклатураИД"]]["ID"],
										'PRODUCT_XML_ID' => $prod["НоменклатураИД"],
										'NAME' => $arProducts[$prod["НоменклатураИД"]]['NAME'],
										'QUANTITY' => $prod["Количество"],
										'PRODUCT_PROVIDER_CLASS' => '',
										'CURRENCY' => "RUB",
										'LID' => "s1",
										'PRICE' => $prod["ЦенаПродажи"],
										'DISCOUNT_PRICE' => $prod["ЦенаПрайс"] - $prod["ЦенаПродажи"],
										'CUSTOM_PRICE' => 'Y',
									);
									
									$arProductParams = array();										
									$arProductParams[] = array(
										'NAME' => 'PRODUCT.XML_ID',
										'CODE' => 'PRODUCT.XML_ID',
										'VALUE' => $prod["НоменклатураИД"],
										'SORT' => 100,
									);
									
									if($prod["ЭтоПодарок"] == 1)
									{
										$arProductParams[] = array(
											'NAME' => 'ЭтоПодарок',
											'CODE' => 'ЭтоПодарок',
											'VALUE' => 1,
											'SORT' => 100,
										);
									}
								
									// если есть в корзине
									if($item = $basket->getExistsItem('catalog', $arProducts[$prod["НоменклатураИД"]]["ID"], ($arProductParams?$arProductParams:[])))
									{
								
										if($item->getQuantity() != $fields["QUANTITY"])
											$item->setField('QUANTITY', $fields["QUANTITY"]);
								
										if($item->getPrice() != $fields["PRICE"])
											$item->setField('PRICE', $fields["PRICE"]);
										
										if($item->getDiscountPrice() != $fields["DISCOUNT_PRICE"])
											$item->setField('DISCOUNT_PRICE', $fields["DISCOUNT_PRICE"]);
									}
									elseif($item = $basket->createItem('catalog', $arProducts[$prod["НоменклатураИД"]]["ID"]))
									{
										$item->setFields($fields);

										$property = $item->getPropertyCollection();
										$property->setProperty($arProductParams);

										$itemSave = $item->save();

										if(!$itemSave->isSuccess())
										{
											echo $itemSave;
										}
									}
									unset($arBasketToDelete[$prod["НоменклатураИД"]]);
								}
							}
							else
							{
								// услуги не заносим, т.к. хз че это
							}
						}
					}
					
					// теперь пройдемся и удалим лишние товары из корзины
					if(!empty($arBasketToDelete))
					{
						foreach($arBasketToDelete as $prodXmlId => $basketId)
						{
							$basket->getItemById($basketId)->delete();
						}
					}
					
					$basket->save();
				}
			}
			
			// END: ***** Разбиремся с корзиной + доставкой
			// ***** ДОСТАВКА
			$priceDeliverySite = $order->getField("PRICE_DELIVERY");
			if(!empty($gateOrder["ТипДоставки"]))
			{
				// тип доставки из CRM
				$deliveryArCrm = $this->getCurrentDelivery($gateOrder["ТипДоставки"], $arDeliveries);
				// тип доставки из заказа с сайта
				$deliveryIdSite = $order->getDeliverySystemId()[0]; // мы считаем, что у нас может быть только один тип оплаты !!!
				// стоимость доставки в заказе
				$priceDeliverySite = $order->getField("PRICE_DELIVERY");

				// если они не совпадают, и в заказе есть типы оплат
				// или изменилась стоимость доставки
				if(
					(
						($deliveryArCrm["ID"] != $deliveryIdSite) || 
						(($priceDeliverySite != $priceDeliveryCrm) && $priceDeliveryCrm > 0)
					)
					&& !empty($deliveryIdSite))
				{
					$shipmentCollection = $order->getShipmentCollection();

					// удалим старые
					foreach ($shipmentCollection as $shipment) {
						
						if($shipment->getField("SYSTEM") == "Y")
							continue;
						
						$shipment->delete();
					}
					// добавляем новый
					$deliv = $shipmentCollection->createItem();
					
					$deliveryParams = array(
						'DELIVERY_ID' => $deliveryArCrm["ID"],
						'DELIVERY_NAME' => $deliveryArCrm["NAME"],
						'PRICE_DELIVERY' => $priceDeliveryCrm,
					);
					
					$deliv->setFields($deliveryParams);
				}
				elseif(
					($deliveryArCrm["ID"] != $deliveryIdSite) ||
					(($priceDeliverySite != $priceDeliveryCrm) && $priceDeliveryCrm > 0)
				)
				{
					$shipmentCollection = $order->getPaymentCollection();
					// добавляем новый
					$deliv = $shipmentCollection->createItem();
					
					$deliveryParams = array(
						'DELIVERY_ID' => $deliveryArCrm["ID"],
						'DELIVERY_NAME' => $deliveryArCrm["NAME"],
						'PRICE_DELIVERY' => $priceDeliveryCrm,
					);
					
					$deliv->setFields($deliveryParams);
				}
				
			}
			
			// ***** END: ДОСТАВКА
			
			$order->save();
		}
		
		// запомним обновление пакетов
		if($this->packetId > 0)
		{
			$arData = json_encode(array(
				"SiteID"=>"10ed05aa-e8ce-45c6-a116-7eab2cc38220",
				"NumberReceived"=>$this->packetId,
			));
			
			$this->gateRes2 = $this->connection->getRest("crm/order/unregisterChanges", array("data" => $arData), "POST", true)["data"];
		}
	}

	/*
	 * Получаем все свойства заказа привязанные к типу плательщика
	 */
	function getPropMatrix($personType = 0)
	{
		$props = array();
		
		$db_props = \CSaleOrderProps::GetList(array(),array());

		while ($obProps = $db_props->Fetch())
		{
			$props[$obProps["PERSON_TYPE_ID"]][$obProps["CODE"]] = $obProps["ID"];
		}
		return $props;
	}
	
	/*
	 * Получаем все оплаты
	 */
	function getPayments()
	{
		$payment = array();

		$db_ptype = \CSalePaySystem::GetList(Array(), Array("ACTIVE"=>"Y"));
		while ($ptype = $db_ptype->Fetch())
		{
			$payment[$ptype["NAME"]] = $ptype["ID"];
		}
		
		return $payment;
	}
	
	/*
	 * Получаем нужную оплату
	 */
	function getCurrentPayment($namePayment = "", $payments = array())
	{
		if(empty($namePayment) || empty($payments))
			return false;
		
		$paidTypes = array(
			'Наличные' => 'Наличная оплата',
			'Безнал' => 'Банковский перевод',
			'Перевод' => 'Банковский перевод',
			'Онлайн' => 'Онлайн оплата',
			'Кредит' => 'Кредит',
		);
		
		return array(
			"ID" => $payments[$paidTypes[$namePayment]],
			"NAME" => $paidTypes[$namePayment]
		);
	}
	
	/*
	 * Получаем все доставки
	 */
	function getDeliveries()
	{
		$deliveries = array();
		
		$db_dtype = \CSaleDelivery::GetList(array(),array("ACTIVE" => "Y"));
		while ($ar_dtype = $db_dtype->Fetch())
		{
			$deliveries[$ar_dtype["NAME"]] = $ar_dtype["ID"];
		}
		
		return $deliveries;
	}
	
	/*
	 * Получаем нужную доставку
	 */
	function getCurrentDelivery($nameDelivery = "",  $deliveries = array())
	{
		if(empty($nameDelivery) || empty($deliveries))
			return false;
		
		$deliveryTypes = array(
			'ТранспортнаяКомпания' => 'Доставка Русклимат',
			'Курьер' => 'Доставка Русклимат',
			'Самовывоз' => 'Самовывоз',
		);
		
		return array(
			"ID" => $deliveries[$deliveryTypes[$nameDelivery]],
			"NAME" => $deliveryTypes[$nameDelivery]
		);
	}
	
	/*
	 * Все статусы заказов
	 */
	function getStatusID($nameStatus)
	{
		if(empty($nameStatus))
			return false;
		
		$status = array();
		
		$db_status = \CSaleStatus::GetList(array(),array("ACTIVE" => "Y"));
		while ($ar_status = $db_status->Fetch())
		{
			$status[$ar_status["NAME"]] = $ar_status["ID"];
			
		}
		
		return $status[$nameStatus];
	}
	
	/*
	 * Получаем товары из заказов
	 */
	function getBasketToDelete($orderId = 0)
	{
		if(empty($orderId))
			return false;
		
		$arBasket = array();

		$dbBasketItems = \CSaleBasket::GetList(array(),array("ORDER_ID"=>$orderId), false, false, array("PRODUCT_XML_ID", "ID"));
		while ($arItems = $dbBasketItems->Fetch())
		{
			$arBasket[$arItems["PRODUCT_XML_ID"]] = $arItems["ID"];
		}
		
		return $arBasket;
	}
}