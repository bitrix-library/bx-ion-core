<?php

namespace Ion;

use \Bitrix\Main,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Application,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Context,
	\Bitrix\Currency\CurrencyManager,
	\Bitrix\Sale,
	\Bitrix\Sale\Basket,
	\Bitrix\Sale\BasketItem,
	\Bitrix\Sale\Discount,
	\Bitrix\Sale\Fuser,
	//\Bitrix\Sale\DiscountCouponsManager,
	\Bitrix\Sale\Order,
	\Bitrix\Sale\Delivery,
	\Bitrix\Sale\PaySystem;

/**
 * @class Ion
 * @pattern Singleton
 */
class Ion {
	
	private static $instance;
	private $context;
	private $request;
	
	/**
	 * @return mixed
	 */
	public static function getInstance() {
		if (static::$instance === null) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	private function __construct() {
		$this->context = Application::getInstance()->getContext();
		$this->request = $this->context->getRequest();
	}
	
	public static function connectOnAfterEpilog() {
		$instance = Ion::getInstance();
		$instance->registerRequestHandlers();
	}
	
	public function registerRequestHandlers() {
		// <HANDLER> : get_ion_status
		if ($this->request['action'] == 'get_ion_status') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$ion = $this->getIonStatus();
			
			echo json_encode($ion);
		}
		// </HANDLER>
		
		// <HANDLER> : add_product_to_basket
		if ($this->request['action'] == 'add_product_to_basket') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$product_id = intval($this->request['product_id']);
			$quantity = intval($this->request['quantity']);
			
			$count = $this->addProductToBasket($product_id, $quantity);
			
			echo json_encode($count);
		}
		// </HANDLER>
		
		// <HANDLER> : change_product_quantity_in_basket
		if ($this->request['action'] == 'change_product_quantity_in_basket') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$product_id = intval($this->request['product_id']);
			$quantity = intval($this->request['quantity']);
			
			$msg = $this->changeProductQuantityInBasket($product_id, $quantity);
			
			echo json_encode($msg);
		}
		// </HANDLER>
		
		// <HANDLER> : remove_product_from_basket
		if ($this->request['action'] == 'remove_product_from_basket') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$product_id = intval($this->request['product_id']);
			
			$msg = $this->removeProductFromBasket($product_id);
			
			echo json_encode($msg);
		}
		// </HANDLER>
		
		// <HANDLER> : get_items_from_basket
		if ($this->request['action'] == 'get_items_from_basket') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$items = $this->getItemsFromBasket();
			
			echo json_encode($items);
		}
		// </HANDLER>
		
		// <HANDLER> : get_basket_info
		if ($this->request['action'] == 'get_basket_info') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$info = $this->getBasketInfo();
			
			echo json_encode($info);
		}
		// </HANDLER>
		
		// <HANDLER> : get_currency_format
		if ($this->request['action'] == 'get_currency_format') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$price = floatval($this->request['price']);
			
			$msg = $this->getCurrencyFormat($price);
			
			echo json_encode($msg);
		}
		// </HANDLER>
		
		// <HANDLER> : get_order_form_groups
		if ($this->request['action'] == 'get_order_form_groups') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$groups = $this->getOrderFormGroups();
			
			echo json_encode($groups);
		}
		// </HANDLER>
		
		// <HANDLER> : order_make_order
		if ($this->request['action'] == 'order_make_order') {
			$GLOBALS['APPLICATION']->RestartBuffer();
			
			$delivery_service_id = intval($this->request["delivery_service_id"]);
			$pay_system_id = intval($this->request["pay_system_id"]);
			$person_type_id = intval($this->request["person_type_id"]);
			$values = Util::mapToArray(json_decode($this->request["values"]));
			
			$order_id = $this->orderMakeOrder($pay_system_id, $delivery_service_id, $person_type_id, $values);
			
			echo json_encode($order_id);
		}
		// </HANDLER>
	}
	
	/**
	 * @return array
	 */
	public function getIonStatus() {
		$ion = [
			'Ion' => [
				'status' => true
			]
		];
		
		return $ion;
	}
	
	/**
	 * @param $product_id
	 * @param $quantity
	 * @return int
	 */
	public function addProductToBasket($product_id, $quantity) {
		if (!Loader::includeModule('sale')) die();
		
		if(!$product_id || !$quantity) die();
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());
		
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
					'CURRENCY' => CurrencyManager::getBaseCurrency(),
					'LID' => $this->context->getSite(),
					'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
				]
			);
			$basket->save();
		}
		
		$count = count($basket->getListOfFormatText());
		
		return $count;
	}
	
	/**
	 * @param $product_id
	 * @param $quantity
	 * @return mixed
	 */
	public function changeProductQuantityInBasket($product_id, $quantity) {
		if (!Loader::includeModule('sale')) die();
		
		$msg['status'] = false;
		
		if(!$product_id || !$quantity) die();
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());
		
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
					'CURRENCY' => CurrencyManager::getBaseCurrency(),
					'LID' => $this->context->getSite(),
					'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
				]
			);
			$basket->save();
			
			$msg['status'] = true;
			$msg['action'] = 'add';
		}
		
		return $msg;
	}
	
	/**
	 * @param $product_id
	 * @return mixed
	 */
	public function removeProductFromBasket($product_id) {
		if (!Loader::includeModule('sale')) die();
		
		$msg['status'] = false;
		
		if(!$product_id) die();
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());
		
		if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {
			
			$basketItem->delete();
			$basket->save();
			
			$msg['status'] = true;
		}
		
		return $msg;
	}
	
	/**
	 * @return array
	 */
	public function getItemsFromBasket() {
		if (!Loader::includeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$items = [];
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());
		
		// <DISCOUNTS> : apply
		$discounts_context = new Discount\Context\Fuser(Fuser::getId());
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		$result = $discounts->calculate()->getData();
		$basket->applyDiscount($result['BASKET_ITEMS']);
		// </DISCOUNTS>
		
		$basket_items = $basket->getBasketItems();
		
		foreach ($basket_items as $obj) {
			$item = [];
			$item['PRODUCT_ID'] = $obj->getProductId();
			$item['PRICE'] = $obj->getPrice();
			$item['SUM_PRICE'] = $obj->getFinalPrice();
			$item['CURRENCY'] = $obj->getCurrency();
			$item['QUANTITY'] = $obj->getQuantity();
			$item['WEIGHT'] = $obj->getWeight();
			$item['FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($item['PRICE'], $item['CURRENCY']);
			$item['SUM_FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($item['SUM_PRICE'], $item['CURRENCY']);
			
			// Получение IBLOCK_ID элемента с которым связан продукт
			$db_iblock_list = \CIBlockElement::GetById($item['PRODUCT_ID']);
			if ($db_iblock_el = $db_iblock_list->GetNext()) {
				$item['PRODUCT_IBLOCK_ID'] = $db_iblock_el['IBLOCK_ID'];
			}
			unset($db_iblock_list);
			
			$allowed_fields_iblock = [
				'ID',
				'IBLOCK_ID',
				'NAME',
				'PREVIEW_PICTURE',
				'DETAIL_PAGE_URL',
			]; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
			
			if (count($GLOBALS['ION']['BASKET_ALLOWED_FIELDS_IBLOCK']) > 0) {
				$allowed_fields_iblock = array_merge($GLOBALS['ION']['BASKET_ALLOWED_FIELDS_IBLOCK'], $allowed_fields_iblock);
			}
			
			// Получение всех полей элемента с которым связан продукт
			$db_iblock_list = \CIBlockElement::GetList(
				[],
				['IBLOCK_ID' => $item['PRODUCT_IBLOCK_ID'], 'ID' => $item['PRODUCT_ID']],
				false,
				false,
				$allowed_fields_iblock
			);
			if ($db_iblock_el = $db_iblock_list->GetNext()) {
				// Получение картинки и изменение ее размеров
				$db_iblock_el['PREVIEW_PICTURE'] = \CFile::ResizeImageGet($db_iblock_el["PREVIEW_PICTURE"], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
				$item['PRODUCT'] = $db_iblock_el;
			}
			unset($db_iblock_list);
			
			$items[] = $item;
		}
		
		unset($db_basket_list);
		
		return $items;
	}
	
	/**
	 * @return array
	 */
	public function getBasketInfo() {
		if (!Loader::includeModule('sale')) die();
		if (!Loader::includeModule('iblock')) die();
		
		$info = [];
		
		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());
		
		// <DISCOUNTS> : apply
		$discounts_context = new Discount\Context\Fuser(Fuser::getId());
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		$result = $discounts->calculate()->getData();
		$basket->applyDiscount($result['BASKET_ITEMS']);
		// </DISCOUNTS>
		
		$info['PRICE'] = $basket->getPrice();
		$info['PRICE_WITHOUT_DISCOUNTS'] = $basket->getBasePrice();
		$info['WEIGHT'] = $basket->getWeight();
		$info['VAT_RATE'] = $basket->getVatRate();
		$info['VAT_SUM'] = $basket->getVatSum();
		$info['FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($info['PRICE'], \CCurrency::GetBaseCurrency());
		$info['FORMATTED_PRICE_WITHOUT_DISCOUNTS'] = \CCurrencyLang::CurrencyFormat($info['PRICE_WITHOUT_DISCOUNTS'], \CCurrency::GetBaseCurrency());
		$info['ITEMS_QUANTITY'] = $basket->getQuantityList();
		$info['QUANTITY'] = count($info['ITEMS_QUANTITY']);
		
		return $info;
	}
	
	/**
	 * @param $price
	 * @param null $currency
	 * @return array
	 */
	public function getCurrencyFormat($price, $currency = null) {
		if (!Loader::includeModule('sale')) die();
		
		$msg = [];
		$msg['status'] = false;
		
		if (!$price) die();
		
		if(!$currency) {
			$currency = \CCurrency::GetBaseCurrency();
		}
		
		$msg['FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($price, $currency);
		$msg['status'] = true;
		
		return $msg;
	}
	
	/**
	 * @return array
	 */
	public function getOrderFormGroups() {
		if (!Loader::includeModule('sale')) die();
		
		// <PROPS>
		$props = [];
		$db_list = \CSaleOrderProps::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y'], false, false, ['ID', 'CODE', 'PROPS_GROUP_ID', 'NAME', 'REQUIED']);
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
			$service['LOGOTIP'] = \CFile::ResizeImageGet($service['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
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
			$system['LOGOTIP'] = \CFile::ResizeImageGet($system['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$props[] = $system;
		}
		
		$payment_group = ['ID' => 'PAYMENT', 'NAME' => 'PAYMENT', 'SORT' => '200'];
		// </PAYMENT>
		
		// <GROUPS>
		$groups = [];
		$db_list = \CSaleOrderPropsGroup::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y', '!ID' => $GLOBALS['ION']['DENY_GROUPS_IDS']]);
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
		
		return $groups;
	}
	
	/**
	 * @param $pay_system_id
	 * @param $delivery_service_id
	 * @param $person_type_id
	 * @param $values
	 * @return mixed
	 */
	public function orderMakeOrder($pay_system_id, $delivery_service_id, $person_type_id, $values) {
		if (!Loader::includeModule('sale')) die();
		
		if (!$pay_system_id || !$person_type_id || !$values || !$delivery_service_id) die();
		
		// <USER>
		$user_id = \CUser::GetID();
		if ($user_id === null) {
			$user_id = CSaleUser::GetAnonymousUserID();
		}
		// </USER>
		
		$allowed_fields = ['NAME', 'LASTNAME', 'EMAIL', 'PHONE'];
		if (count($GLOBALS['ION']['ORDER_ALLOWED_FIELDS']) > 0) {
			$allowed_fields = array_merge($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'], $allowed_fields);
		}
		
		//DiscountCouponsManager::init();
		
		$order = Order::create($this->context->getSite(), $user_id);
		$order->setPersonTypeId($person_type_id);
		$basket = Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), $this->context->getSite())->getOrderableItems();
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
		
		$currencyCode = Option::get('sale', 'default_currency', 'RUB', $this->context->getSite());
		$order->setField('CURRENCY', $currencyCode);
		
		foreach ($propertyCollection as $el) {
			if ($values[$el->getField('CODE')] && in_array($el->getField('CODE'), $allowed_fields)) {
				$el->setValue($values[$el->getField('CODE')]);
			}
		}
		
		$order->save();
		$order_id = $order->GetId();
		
		return $order_id;
	}
}