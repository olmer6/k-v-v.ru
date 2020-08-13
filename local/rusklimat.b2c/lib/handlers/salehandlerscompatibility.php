<?php
namespace Rusklimat\B2c\Handlers;

use Bitrix\Main;
use Bitrix\Main\Entity;

class SaleHandlersCompatibility
{
	public static function Init()
	{
		\AddEventHandler('sale', 'OnBeforeBasketAdd', [__CLASS__, 'OnBeforeBasketAddHandler']);
		\AddEventHandler('sale', 'OnBasketAdd', [__CLASS__, 'OnBasketAddHandler']);
		\AddEventHandler('sale', 'OnBeforeBasketUpdate', [__CLASS__, 'OnBeforeBasketUpdateHandler']);
		\AddEventHandler('sale', 'OnBasketUpdate', [__CLASS__, 'OnBasketUpdateHandler']);
		\AddEventHandler('sale', 'OnBasketDelete', [__CLASS__, 'OnBasketDeleteHandler']);
		\AddEventHandler('sale', 'OnOrderSave', [__CLASS__, 'OnOrderSaveHandler']);
		\AddEventHandler('sale', 'OnSaleComponentOrderOneStepProcess', [__CLASS__, 'OnSaleComponentOrderOneStepProcess']);
	}

	function OnBeforeBasketAddHandler(&$arFields)
	{
		$arFields["PRODUCT_PROVIDER_CLASS"] = "RKCatalogProductProvider";
	}

	function OnBasketAddHandler($ID, $arFields)
	{
		$send = true;

		if ($_SESSION["basket_from_gateway"] == 1) {
			$_SESSION["basket_from_gateway"] = 0;
			$send = false;
		}

		if ($send) {
			\SendDataToExternalServer("BASKET", array());
		}
	}

	function OnBeforeBasketUpdateHandler($ID, &$arFields)
	{
		$arFields["PRODUCT_PROVIDER_CLASS"] = "RKCatalogProductProvider";
	}

	function OnBasketUpdateHandler($ID, $arFields)
	{
		$send = true;

		if ($_SESSION["basket_from_gateway"] == 1) {
			$_SESSION["basket_from_gateway"] = 0;
			$send = false;
		}

		if ($send) {
			\SendDataToExternalServer("BASKET", array());
		}
	}

	function OnBasketDeleteHandler($ID)
	{
		//\AddMessage2Log("OnBasketDeleteHandler", "OnBasketDeleteHandler");

		$send = true;
		if ($_SESSION["basket_from_gateway"] == 1) {
			$_SESSION["basket_from_gateway"] = 0;
			$send = false;
		}
		if ($send) {
			\SendDataToExternalServer("BASKET", array());
		}
	}

	/**
	 * @param $orderId
	 * @param $arFields
	 * @param $arOrder
	 * @param $isNew
	 */
	function OnOrderSaveHandler($orderId = 0, $arFields = [], $arOrder = [], $isNew = false)
	{
		GLOBAL $USER_FIELD_MANAGER;

		if($isNew)
		{
			\CEventLog::Log('INFO', 'ORDER_CREATED', 'rusklimat.b2c', $orderId, serialize($arFields));

			/* Актуализируем корзину и заказа из crm */

			$fullDiscount = 0;

			$dbBasketItems = \CSaleBasket::GetList(["NAME" => "ASC", "ID" => "ASC"], ["ORDER_ID" => $orderId], false, false, []);

			while($arItems = $dbBasketItems->Fetch())
			{
				$arEl = \CIBlockElement::GetList(
					["ID", "PROPERTY_NS_CODE"],
					["ID" => $arItems["PRODUCT_ID"]],
					false,
					false,
					["ID", "PROPERTY_NS_CODE"]
				)->GetNext();

				if ($arEl)
				{
					$arCallbackPrice = \RKCatalogProductProvider::GetProductData([
						'PRODUCT_ID' => $arItems["PRODUCT_ID"],
						'QUANTITY' => $arItems["QUANTITY"],
						'RENEWAL' => 'N'
					]);

					if (!empty($arCallbackPrice))
					{
						$arItems["PRICE"] = $arItems['PRICE'] - $arCallbackPrice['DISCOUNT_PRICE'];
						$arItems["DISCOUNT_PRICE"] = $arCallbackPrice['DISCOUNT_PRICE'];

						\CSaleBasket::Update($arItems["ID"], [
							"PRICE" => $arItems['PRICE'],
							"DISCOUNT_PRICE" => $arItems['DISCOUNT_PRICE'],
						]);

						$fullDiscount += $arCallbackPrice['DISCOUNT_PRICE'] * $arItems["QUANTITY"];
					}
				}
			}

			$arOrderProps = [];

			$rsOrderProps = \CSaleOrderPropsValue::GetOrderProps($orderId);

			while($arProp = $rsOrderProps->Fetch())
			{
				$arOrderProps[$arProp['CODE']] = $arProp;
			}

			$arOrderUpdateFields = array(
				"PRICE_DELIVERY" => $arOrderProps['HIDE_DELIVERY_PRICE']['VALUE'],
				"PAY_SYSTEM_ID" => $_POST['PAY_SYSTEM_ID'],
				"USER_DESCRIPTION" => $_POST['ORDER_DESCRIPTION'],
				"DELIVERY_ID" => $_POST['DELIVERY_ID'],
				"PRICE" => $arOrder["PRICE"] - $fullDiscount,
			);

			if(!empty($_SESSION['COUPON']))
			{
				if(!empty($arOrderProps['HIDE_BASKET_COUPON']))
				{
					\CSaleOrderPropsValue::Update(
						$arOrderProps['HIDE_BASKET_COUPON']['ID'],
						['VALUE' => $_SESSION['COUPON']]
					);
				}
				else
				{
					$orderPropCoupon = \Bitrix\Sale\Internals\OrderPropsTable::getList([
						'select' => ['ID', 'NAME', 'CODE'],
						'filter' => [
							'=PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'],
							'=CODE' => 'HIDE_BASKET_COUPON',
						],
						'limit' => 1
					])->fetch();

					if($orderPropCoupon)
					{
						\CSaleOrderPropsValue::Add([
							'ORDER_ID' => $orderId,
							'ORDER_PROPS_ID' => $orderPropCoupon['ID'],
							'NAME' => $orderPropCoupon['NAME'],
							'CODE' => $orderPropCoupon['CODE'],
							'VALUE' => $_SESSION['COUPON'],
						]);
					}
				}
			}
			
			// сохранение файла для юриков
			if(
				!empty($arOrderProps['HIDE_FILE_STRING']['VALUE']) &&
				empty($arOrderProps['FILE']['VALUE'])
				// еще тип пользователя можно проверять
			)
			{
				$arFile = \CFile::MakeFileArray($arOrderProps['HIDE_FILE_STRING']['VALUE']);
				$fid = \CFile::SaveFile($arFile, "sale/order/properties");

				\CSaleOrderPropsValue::Add(array(
					'NAME' => "Вложить файл",
					'CODE' => "FILE",
					'ORDER_PROPS_ID' => 23,
					'ORDER_ID' => $orderId,
					'VALUE' => $fid
				));
			}

			\CSaleOrder::Update($orderId, $arOrderUpdateFields);

			/* Отправляем заказ в crm */

			if(!isDev())
			{
				$sendToCrm = new \Rusklimat\B2c\Helpers\Sale\Order\SendToCrm($orderId);
				$sendToCrmResult = $sendToCrm->send();
				$sendToCrm->sendEmail();

				if($sendToCrmResult)
				{
					$USER_FIELD_MANAGER->Update('ORDER', $orderId, ['UF_ORDER_1C_SENDED' => 1]);
				}
			}
		}

		unset($_SESSION['COUPON']);

		if(!empty($arFields['USER_ID']))
		{
			$user = new \CUser;

			$arUpdateUserFields = [];

			/* Подписка */

			if($_REQUEST['USER_UF_SUBSCRIPTION'] == 'Y')
				$arUpdateUserFields['UF_SUBSCRIPTION'] = 1;
			else
				$arUpdateUserFields['UF_SUBSCRIPTION'] = 0;
			
			// название компании
			if(!empty($arOrder["ORDER_PROP"][8]))
				$arUpdateUserFields["WORK_COMPANY"] = $arOrder["ORDER_PROP"][8];
			
			// ИНН
			if(!empty($arOrder["ORDER_PROP"][10]))
				$arUpdateUserFields["UF_UR_LICO_INN"] = $arOrder["ORDER_PROP"][10];
			
			// КПП
			if(!empty($arOrder["ORDER_PROP"][11]))
				$arUpdateUserFields["UF_UR_LICO_KPP"] = $arOrder["ORDER_PROP"][11];
			
			// файл с реквизитами
			if(!empty($arOrder["ORDER_PROP"][23][0]["ID"]))
				$arUpdateUserFields["UF_UR_LICO_FILE"] = $arOrder["ORDER_PROP"][23][0]["ID"];

			if(!empty($arUpdateUserFields))
			{
				$user->Update($arFields['USER_ID'], $arUpdateUserFields);
			}
		}
	}

	function OnSaleComponentOrderOneStepProcess(&$arResult = [], &$arUserResult = [], $arParams = [])
	{
		GLOBAL $APPLICATION;

		if(\Rusklimat\B2c\Helpers\Sale\Order\Captcha::isRequired())
		{
			if (!$APPLICATION->CaptchaCheckCode($_POST["captcha_word"], $_POST["captcha_sid"]))
			{
				$arResult['ERROR']['CAPTCHA'] = 'Введите слово на картинке';
			}
		}
	}
}