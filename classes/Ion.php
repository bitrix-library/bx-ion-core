<?php

use Bitrix\Sale\Basket,
	Bitrix\Sale\BasketItem,
	Bitrix\Sale\Fuser,
	Bitrix\Main\Context,
	Bitrix\Main\Loader,
	Bitrix\Sale\DiscountCouponsManager,
	Bitrix\Sale\Order,
	Bitrix\Main\Config\Option,
	Bitrix\Sale\Delivery,
	Bitrix\Sale\PaySystem,
	Bitrix\Sale,
	Bitrix\Main;

class Ion {
	
	private static $iblock_properties_to_return = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_ARTNUMBER']; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
	private static $basket_properties_to_return = ['PRODUCT_ID', 'QUANTITY', 'PRICE', 'WEIGHT', 'CURRENCY']; // Если необходимо получить все поля: ['*']
	
	public static function connectOnAfterEpilog(){
		static::registerRequestHandlers();
		return;
	}
	
	public static function registerRequestHandlers(){
		/**
		 * Handler: ion
		 */
		if ($_REQUEST['action'] == 'get_ion_status') {
			static::getIonStatus();
		}
		
		/**
		 * Handler: add_product_to_basket
		 */
		if ($_REQUEST['action'] == 'add_product_to_basket') {
			static::addProductToBasket();
		}
		
		/**
		 * Handler: change_product_quantity_in_basket
		 */
		if ($_REQUEST['action'] == 'change_product_quantity_in_basket') {
			static::changeProductQuantityInBasket();
		}
		
		/**
		 * Handler: remove_product_from_basket
		 */
		if ($_REQUEST['action'] == 'remove_product_from_basket') {
			static::removeProductFromBasket();
		}
		
		/**
		 * Handler: get_items_from_basket
		 */
		if ($_REQUEST['action'] == 'get_items_from_basket') {
			static::getItemsFromBasket();
		}
		
		/**
		 * Handler: get_basket_info
		 */
		if ($_REQUEST['action'] == 'get_basket_info') {
			static::getBasketInfo();
		}
		
		/**
		 * Handler: get_currency_format
		 */
		if ($_REQUEST['action'] == 'get_currency_format') {
			static::getCurrencyFormat();
		}
		
		/**
		 * Handler: get_order_form_groups
		 */
		if ($_REQUEST['action'] == 'get_order_form_groups') {
			static::getOrderFormGroups();
		}
		
		/**
		 * Handler: order_make_order
		 */
		if ($_REQUEST['action'] == 'order_make_order') {
			static::orderMakeOrder();
		}
	}
	
	public static function getIonStatus(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		echo json_encode([
			'Ion' => [
				'status' => true
			]
		]);
		return;
	}
	
	public static function addProductToBasket(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$product_id = intval($_REQUEST['product_id']);
		$quantity = intval($_REQUEST['quantity']);
		
		if($product_id && $quantity) {
			
			$basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
			
			if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {
				
				// Обновление товара в корзине
				$basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
				$basket->save();
				
			} else {
				
				// Добавление товара в корзину
				$basketItem = $basket->createItem('catalog', $product_id);
				$basketItem->setFields(
					[
						'QUANTITY' => $quantity,
						'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
						'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
						'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
					]
				);
				$basket->save();
			}
			
			echo count($basket->getListOfFormatText());
		}
		
		return;
	}
	
	public static function changeProductQuantityInBasket(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg['status'] = false;
		
		$product_id = intval($_REQUEST['product_id']);
		$quantity = intval($_REQUEST['quantity']);
		
		if($product_id && $quantity) {
			
			$basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
			
			if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {
				
				// Обновление товара в корзине
				$basketItem->setField('QUANTITY', $quantity);
				$basket->save();
				
				$msg['status'] = true;
				$msg['action'] = 'update';
				
			} else {
				
				// Добавление товара в корзину
				$basketItem = $basket->createItem('catalog', $product_id);
				$basketItem->setFields(
					[
						'QUANTITY' => $quantity,
						'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
						'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
						'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
					]
				);
				$basket->save();
				
				$msg['status'] = true;
				$msg['action'] = 'add';
			}
		}
		
		echo json_encode($msg);
		return;
	}
	
	public static function removeProductFromBasket(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg['status'] = false;
		
		$product_id = intval($_REQUEST['product_id']);
		
		if($product_id) {
			
			$basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
			
			if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {
				
				$basketItem->delete();
				$basket->save();
				
				$msg['status'] = true;
			}
		}
		
		echo json_encode($msg);
		return;
	}
	
	public static function getItemsFromBasket(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$items = [];
		
		$db_basket_list = Basket::getList([
			'select' => static::$basket_properties_to_return,
			'filter' => [
				'=FUSER_ID' => Fuser::getId(),
				'=ORDER_ID' => null,
				'=LID' => Context::getCurrent()->getSite(),
				'=CAN_BUY' => 'Y',
			]
		]);
		
		while ($db_basket_el = $db_basket_list->fetch())
		{
			
			// Получение IBLOCK_ID элемента с которым связан продукт
			$db_iblock_list = CIBlockElement::GetById($db_basket_el['PRODUCT_ID']);
			if ($db_iblock_el = $db_iblock_list->GetNext()) {
				$db_basket_el['PRODUCT_IBLOCK_ID'] = $db_iblock_el['IBLOCK_ID'];
			}
			unset($db_iblock_list);
			
			// Получение всех полей элемента с которым связан продукт
			$db_iblock_list = CIBlockElement::GetList(
				[],
				['IBLOCK_ID' => $db_basket_el['PRODUCT_IBLOCK_ID'], 'ID' => $db_basket_el['PRODUCT_ID']],
				false,
				false,
				static::$iblock_properties_to_return
			);
			if ($db_iblock_el = $db_iblock_list->GetNext()) {
				// Получение картинки и изменение ее размеров
				$db_iblock_el['PREVIEW_PICTURE'] = CFile::ResizeImageGet($db_iblock_el["PREVIEW_PICTURE"], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
				$db_basket_el['PRODUCT'] = $db_iblock_el;
			}
			unset($db_iblock_list);
			
			$db_basket_el['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($db_basket_el['PRICE'], $db_basket_el['CURRENCY']);
			$db_basket_el['SUM_FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($db_basket_el['PRICE'] * $db_basket_el['QUANTITY'], $db_basket_el['CURRENCY']);
			
			$items[] = $db_basket_el;
		}
		
		unset($db_basket_list);
		
		echo json_encode($items);
		return;
	}
	
	public static function getBasketInfo(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$info = [];
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
		
		$info['PRICE'] = $basket->getPrice();
		$info['PRICE_WITHOUT_DISCOUNTS'] = $basket->getBasePrice();
		$info['WEIGHT'] = $basket->getWeight();
		$info['VAT_RATE'] = $basket->getVatRate();
		$info['VAT_SUM'] = $basket->getVatSum();
		$info['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($info['PRICE'], CCurrency::GetBaseCurrency());
		$info['FORMATTED_PRICE_WITHOUT_DISCOUNTS'] = CCurrencyLang::CurrencyFormat($info['PRICE_WITHOUT_DISCOUNTS'], CCurrency::GetBaseCurrency());
		$info['ITEMS_QUANTITY'] = $basket->getQuantityList();
		$info['QUANTITY'] = count($info['ITEMS_QUANTITY']);
		
		echo json_encode($info);
		return;
	}
	
	public static function getCurrencyFormat(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg = [];
		$msg['status'] = false;
		
		
		$price = floatval($_REQUEST['price']);
		$currency = CCurrency::GetBaseCurrency();
		
		if($_REQUEST['currency']) {
			$currency = htmlspecialchars($_REQUEST['currency']);
		}
		
		if ($price && $currency) {
			$msg['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($price, $currency);
			$msg['status'] = true;
		}
		
		echo json_encode($msg);
		return;
	}
	
	public static function getOrderFormGroups(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$props = [];
		$db_list = CSaleOrderProps::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y'], false, false, ['ID', 'CODE', 'PROPS_GROUP_ID', 'NAME', 'REQUIED']);
		while ($db_el = $db_list->GetNext()) {
			$props[] = $db_el;
		}
		
		$groups = [];
		$db_list = CSaleOrderPropsGroup::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y', '!ID' => $GLOBALS['ION']['DENY_GROUPS_IDS']]);
		while ($db_el = $db_list->GetNext()) {
			$groups[] = $db_el;
		}
		
		foreach ($groups as $key => &$group) {
			foreach ($props as $prop) {
				if($prop['PROPS_GROUP_ID'] == $group['ID']) {
					$group['PROPS'][] = $prop;
				}
			}
			if(!$group['PROPS']) {
				unset($groups[$key]);
			}
		}
		sort($groups);
		
		echo json_encode($groups);
		return;
	}
	
	public static function orderMakeOrder(){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$pay_system_id = intval($_REQUEST["pay_system_id"]);
		$person_type_id = intval($_REQUEST["person_type_id"]);
		$values = map_to_array(json_decode($_REQUEST["values"]));
		
		if (!$pay_system_id || !$person_type_id || !$values) die();
		
		$allowed_fields = ['NAME', 'LASTNAME', 'EMAIL', 'PHONE'];
		if (count($GLOBALS['ION']['ORDER_ALLOWED_FIELDS']) > 0) {
			$allowed_fields = array_merge($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'], $allowed_fields);
		}
		
		DiscountCouponsManager::init();
		
		$order = Order::create(Context::getCurrent()->getSite(), \CSaleUser::GetAnonymousUserID());
		$order->setPersonTypeId($person_type_id);
		$basket = Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), Context::getCurrent()->getSite())->getOrderableItems();
		$order->setBasket($basket);
		
		/*Shipment*/
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
		$shipment->setFields(array(
			'DELIVERY_ID' => $service['ID'],
			'DELIVERY_NAME' => $service['NAME'],
		));
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		foreach ($order->getBasket() as $item)
		{
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}
		
		/*Payment*/
		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem();
		$paySystemService = PaySystem\Manager::getObjectById($pay_system_id);
		$payment->setFields(array(
			'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
			'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
		));
		
		$order->doFinalAction(true);
		
		$propertyCollection = $order->getPropertyCollection();
		
		$currencyCode = Option::get('sale', 'default_currency', 'RUB', Context::getCurrent()->getSite());
		$order->setField('CURRENCY', $currencyCode);
		
		foreach ($propertyCollection as $el) {
			if ($values[$el->getField('CODE')] && in_array($el->getField('CODE'), $allowed_fields)) {
				$el->setValue($values[$el->getField('CODE')]);
			}
		}
		
		$order->save();
		$order_id = $order->GetId();
		
		echo json_encode($order_id);
		return;
	}
}