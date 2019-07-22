<?php

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Application,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Context,
	Bitrix\Sale,
	Bitrix\Sale\Basket,
	Bitrix\Sale\BasketItem,
	Bitrix\Sale\Fuser,
	//Bitrix\Sale\DiscountCouponsManager,
	Bitrix\Sale\Order,
	Bitrix\Sale\Delivery,
	Bitrix\Sale\PaySystem;

/**
 * Class Ion
 * Pattern Singleton
 */
class Ion {
	
	private static $instance;
	private $context;
	private $request;
	private $iblock_properties_to_return;
	private $basket_properties_to_return;
	
	public static function getInstance() {
		if (static::$instance === null) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	private function __construct() {
		$this->context = Application::getInstance()->getContext();
		$this->request = $this->context->getRequest();
		$this->iblock_properties_to_return = [
			'ID',
			'IBLOCK_ID',
			'NAME',
			'PREVIEW_PICTURE',
			'DETAIL_PAGE_URL',
			'PROPERTY_ARTNUMBER'
		]; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
		$this->basket_properties_to_return = [
			'PRODUCT_ID',
			'QUANTITY',
			'PRICE',
			'WEIGHT',
			'CURRENCY'
		]; // Если необходимо получить все поля: ['*']
	}
	
	public static function connectOnAfterEpilog() {
		$instance = Ion::getInstance();
		$instance->registerRequestHandlers();
		return;
	}
	
	public function registerRequestHandlers() {
		// <HANDLER> : get_ion_status
		if ($this->request['action'] == 'get_ion_status') {
			$this->getIonStatus();
		}
		// </HANDLER>
		
		// <HANDLER> : add_product_to_basket
		if ($this->request['action'] == 'add_product_to_basket') {
			$this->addProductToBasket();
		}
		// </HANDLER>
		
		// <HANDLER> : change_product_quantity_in_basket
		if ($this->request['action'] == 'change_product_quantity_in_basket') {
			$this->changeProductQuantityInBasket();
		}
		// </HANDLER>
		
		// <HANDLER> : remove_product_from_basket
		if ($this->request['action'] == 'remove_product_from_basket') {
			$this->removeProductFromBasket();
		}
		// </HANDLER>
		
		// <HANDLER> : get_items_from_basket
		if ($this->request['action'] == 'get_items_from_basket') {
			$this->getItemsFromBasket();
		}
		// </HANDLER>
		
		// <HANDLER> : get_basket_info
		if ($this->request['action'] == 'get_basket_info') {
			$this->getBasketInfo();
		}
		// </HANDLER>
		
		// <HANDLER> : get_currency_format
		if ($this->request['action'] == 'get_currency_format') {
			$this->getCurrencyFormat();
		}
		// </HANDLER>
		
		// <HANDLER> : get_order_form_groups
		if ($this->request['action'] == 'get_order_form_groups') {
			$this->getOrderFormGroups();
		}
		// </HANDLER>
		
		// <HANDLER> : order_make_order
		if ($this->request['action'] == 'order_make_order') {
			$this->orderMakeOrder();
		}
		// </HANDLER>
	}
	
	private function getIonStatus() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		echo json_encode([
			'Ion' => [
				'status' => true
			]
		]);
		return;
	}
	
	private function addProductToBasket() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$product_id = intval($this->request['product_id']);
		$quantity = intval($this->request['quantity']);
		
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
	
	private function changeProductQuantityInBasket() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg['status'] = false;
		
		$product_id = intval($this->request['product_id']);
		$quantity = intval($this->request['quantity']);
		
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
	
	private function removeProductFromBasket() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg['status'] = false;
		
		$product_id = intval($this->request['product_id']);
		
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
	
	private function getItemsFromBasket() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$items = [];
		
		$db_basket_list = Basket::getList([
			'select' => $this->basket_properties_to_return,
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
				$this->iblock_properties_to_return
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
	
	private function getBasketInfo() {
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
	
	private function getCurrencyFormat() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$msg = [];
		$msg['status'] = false;
		
		
		$price = floatval($this->request['price']);
		$currency = CCurrency::GetBaseCurrency();
		
		if($this->request['currency']) {
			$currency = htmlspecialchars($this->request['currency']);
		}
		
		if ($price && $currency) {
			$msg['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($price, $currency);
			$msg['status'] = true;
		}
		
		echo json_encode($msg);
		return;
	}
	
	private function getOrderFormGroups() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		// <PROPS>
		$props = [];
		$db_list = CSaleOrderProps::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y'], false, false, ['ID', 'CODE', 'PROPS_GROUP_ID', 'NAME', 'REQUIED']);
		while ($db_el = $db_list->GetNext()) {
			$props[] = $db_el;
		}
		unset($db_list);
		// </PROPS>
		
		// <DELIVERY>
		$delivery = Delivery\Services\Manager::getActiveList();
		
		foreach ($delivery as $service) {
			if ($service['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService') {
				continue;
			}
			$service['PROPS_GROUP_ID'] = 'DELIVERY';
			$service['PRICE'] = $service['CONFIG']['MAIN']['PRICE'];
			$service['LOGOTIP'] = CFile::ResizeImageGet($service['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$props[] = $service;
		}
		
		$delivery_group = ['ID' => 'DELIVERY', 'NAME' => 'DELIVERY', 'SORT' => '100'];
		// </DELIVERY>
		
		// <PAYMENT>
		$payment = [];
		$db_list = PaySystem\Manager::getList(
			[
				'select' => ['*'],
				'filter' => [
					'=ACTIVE' => 'Y'
				]
			]
		);
		while ($db_el = $db_list->fetch()) {
			$payment[] = $db_el;
		}
		unset($db_list);
		
		foreach ($payment as $system) {
			$system['PROPS_GROUP_ID'] = 'PAYMENT';
			$system['LOGOTIP'] = CFile::ResizeImageGet($system['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$props[] = $system;
		}
		
		$payment_group = ['ID' => 'PAYMENT', 'NAME' => 'PAYMENT', 'SORT' => '200'];
		// </PAYMENT>
		
		// <GROUPS>
		$groups = [];
		$db_list = CSaleOrderPropsGroup::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y', '!ID' => $GLOBALS['ION']['DENY_GROUPS_IDS']]);
		while ($db_el = $db_list->GetNext()) {
			$groups[] = $db_el;
		}
		unset($db_list);
		$groups[] = $delivery_group;
		$groups[] = $payment_group;
		// </GROUPS>
		
		// <PROPS TO GROUPS>
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
		usort($groups, function ($a, $b) {
			return $a['SORT'] - $b['SORT'];
		});
		// </PROPS TO GROUPS>
		
		echo json_encode($groups);
		return;
	}
	
	private function orderMakeOrder() {
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if (!CModule::IncludeModule('sale')) die();
		
		$delivery_service_id = intval($this->request["delivery_service_id"]);
		$pay_system_id = intval($this->request["pay_system_id"]);
		$person_type_id = intval($this->request["person_type_id"]);
		$values = map_to_array(json_decode($this->request["values"]));
		
		if (!$pay_system_id || !$person_type_id || !$values || !$delivery_service_id) die();
		
		// <USER>
		$user_id = CUser::GetID();
		if ($user_id === null) {
			$user_id = CSaleUser::GetAnonymousUserID();
		}
		// </USER>
		
		$allowed_fields = ['NAME', 'LASTNAME', 'EMAIL', 'PHONE'];
		if (count($GLOBALS['ION']['ORDER_ALLOWED_FIELDS']) > 0) {
			$allowed_fields = array_merge($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'], $allowed_fields);
		}
		
		//DiscountCouponsManager::init();
		
		$order = Order::create(Context::getCurrent()->getSite(), $user_id);
		$order->setPersonTypeId($person_type_id);
		$basket = Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), Context::getCurrent()->getSite())->getOrderableItems();
		$order->setBasket($basket);
		
		// <SHIPMENT>
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		//$service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
		$service = Delivery\Services\Manager::getById($delivery_service_id);
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
		// </SHIPMENT>
		
		// <PAYMENT>
		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem();
		$paySystemService = PaySystem\Manager::getObjectById($pay_system_id);
		$payment->setFields(array(
			'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
			'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
		));
		// </PAYMENT>
		
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