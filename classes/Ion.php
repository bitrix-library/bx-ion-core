<?php

namespace Ion;

use Closure;
use CCurrency;
use CCurrencyLang;
use CIBlockElement;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Discount;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Order;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Catalog\CatalogViewedProductTable;

/**
 * @class Ion
 * @pattern Singleton
 */
class Ion
{

	private static $instance;
	private $context;
	private $request;
	private $module_absolute_path;
	private $module_relative_path;

	/**
	 * @return mixed
	 */
	public static function getInstance(): self
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Ion constructor.
	 */
	private function __construct()
	{
		$this->context = Application::getInstance()->getContext();
		$this->request = $this->context->getRequest();
		$this->module_absolute_path = str_replace("\\", "/", dirname(__DIR__ . '\\..\\'));
		$this->module_relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->module_absolute_path);

		$GLOBALS['ION']['SEARCH_IBLOCK_ID'] = null;
		$GLOBALS['ION']['BASKET_ALLOWED_FIELDS_IBLOCK'] = null;
		$GLOBALS['ION']['DENY_GROUPS_IDS'] = null;
		$GLOBALS['ION']['ORDER_ALLOWED_FIELDS'] = null;
		$GLOBALS['ION']['SEARCH_ALLOWED_FIELDS_IBLOCK'] = null;
		$GLOBALS['ION']['SEARCH_IBLOCK_ID'] = null;
		$GLOBALS['ION']['MAKE_ORDER_HANDLER'] = null;
		$GLOBALS['ION']['CLOSURES'] = null;
	}

	public static function connectOnProlog(): void
	{
		$instance = self::getInstance();
		Asset::getInstance()->addJs($instance->module_relative_path . '/js/Util.js');
	}

	public static function connectOnEpilog(): void
	{
		$instance = self::getInstance();
		$instance->registerRequestHandlers();
	}

	public static function connectOnAfterEpilog(): void
	{
		$instance = self::getInstance();
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function registerRequestHandlers(): void
	{
		if ($this->request['ion'] !== null) {
			$GLOBALS['APPLICATION']->RestartBuffer();

			$result = null;

			switch ($this->request['ion']) {
				case 'get_ion_status':
					$result = $this->getIonStatus();
					break;

				case 'get_closure':
					$id = $this->request['id'];
					$result = $this->getClosure($id);
					break;

				case 'add_product_to_basket':
					$product_id = (int)$this->request['product_id'];
					$quantity = (int)$this->request['quantity'];
					$props = $this->request['props'];
					$result = $this->addProductToBasket($product_id, $quantity, $props);
					break;

				case 'change_product_quantity_in_basket':
					$product_id = (int)$this->request['product_id'];
					$quantity = (int)$this->request['quantity'];
					$props = $this->request['props'];
					$result = $this->changeProductQuantityInBasket($product_id, $quantity, $props);
					break;

				case 'remove_product_from_basket':
					$product_id = (int)$this->request['product_id'];
					$props = $this->request['props'];
					$result = $this->removeProductFromBasket($product_id, $props);
					break;

				case 'get_items_from_basket':
					$result = $this->getItemsFromBasket();
					break;

				case 'get_basket_info':
					$result = $this->getBasketInfo();
					break;

				case 'get_currency_format':
					$price = (float)$this->request['price'];
					$result = $this->getCurrencyFormat($price);
					break;

				case 'get_order_form_groups':
					$result = $this->getOrderFormGroups();
					break;

				case 'order_make_order':
					$delivery_service_id = (int)$this->request["delivery_service_id"];
					$pay_system_id = (int)$this->request["pay_system_id"];
					$person_type_id = (int)$this->request["person_type_id"];
					$values = Util::mapToArray(json_decode($this->request["values"], true));
					$result = $this->createOrder($pay_system_id, $delivery_service_id, $person_type_id, $values);
					break;

				case 'search_items_by_name':
					$result = $this->searchItemsByName($this->request["name"], $this->request["page"]);
					break;
			}

			echo Json::encode($result);
		}
	}

	/**
	 * @return array
	 */
	public function getIonStatus(): array
	{
		return [
			'Ion' => [
				'status' => true
			]
		];
	}

	/**
	 * @param $id
	 * @return null
	 */
	public function getClosure($id)
	{
		if ($GLOBALS['ION']['CLOSURES'][$id] instanceof Closure) {
			return $GLOBALS['ION']['CLOSURES'][$id]();
		}

		return null;
	}

	/**
	 * @param $product_id
	 * @param $quantity
	 * @param $props
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function addProductToBasket($product_id, $quantity, $props): array
	{
		if (!$product_id || !Loader::includeModule('sale')) {
			die();
		}

		if ($quantity === null) {
			$quantity = 1;
		}

		if ($props === null) {
			$props = [];
		}

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		if ($basketItem = $basket->getExistsItem('catalog', $product_id, $props)) {
			// Обновление товара в корзине
			$basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
			$basket->save();
		} else {
			// Добавление товара в корзину
			$basketItem = $basket->createItem('catalog', $product_id);
			$basketItem->setFields([
				'QUANTITY' => $quantity,
				'CURRENCY' => CurrencyManager::getBaseCurrency(),
				'LID' => $this->context->getSite(),
				'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
			]);

			// Добавление свойств товару
			foreach ($props as $prop) {
				$collection = $basketItem->getPropertyCollection();

				$item = $collection->createItem();
				$item->setFields([
					'NAME' => $prop['NAME'],
					'CODE' => $prop['CODE'],
					'VALUE' => $prop['VALUE'],
				]);
			}

			$basket->save();
		}

		$info = $this->getBasketInfo();

		return $info;
	}

	/**
	 * @param $product_id
	 * @param $quantity
	 * @param $props
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function changeProductQuantityInBasket($product_id, $quantity, $props)
	{
		if (!$product_id || !Loader::includeModule('sale')) {
			die();
		}

		if ($quantity === null) {
			$quantity = 1;
		}

		if ($props === null) {
			$props = [];
		}

		$msg['status'] = false;

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		if ($basketItem = $basket->getExistsItem('catalog', $product_id, $props)) {
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

			// Добавление свойств товару
			foreach ($props as $prop) {
				$collection = $basketItem->getPropertyCollection();

				$item = $collection->createItem();
				$item->setFields([
					'NAME' => $prop['NAME'],
					'CODE' => $prop['CODE'],
					'VALUE' => $prop['VALUE'],
				]);
			}

			$basket->save();

			$msg['status'] = true;
			$msg['action'] = 'add';
		}

		return $msg;
	}

	/**
	 * @param $product_id
	 * @param $props
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function removeProductFromBasket($product_id, $props)
	{
		if (!$product_id || !Loader::includeModule('sale')) {
			die();
		}

		$msg['status'] = false;

		if ($props === null) {
			$props = [];
		}

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		if ($basketItem = $basket->getExistsItem('catalog', $product_id, $props)) {

			$basketItem->delete();
			$basket->save();

			$msg['status'] = true;
		}

		return $msg;
	}

	/**
	 * @param null $fuser
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 */
	public function getItemsFromBasket($fuser = null): array
	{
		if (!Loader::includeModule('sale')
			|| !Loader::includeModule('iblock')
			|| !Loader::includeModule('sale')
		) {
			die();
		}

		if ($fuser === null) {
			$fuser = Fuser::getId();
		}

		$items = array();

		$basket = Basket::loadItemsForFUser($fuser, $this->context->getSite());

		// <DISCOUNTS> : apply
		$discounts_context = new Discount\Context\Fuser($fuser);
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		if ($discounts !== null) {
			$result = $discounts->calculate()->getData();
			$basket->applyDiscount($result['BASKET_ITEMS']);
		}
		// </DISCOUNTS>

		$basket_items = $basket->getBasketItems();

		foreach ($basket_items as $obj) {
			$item = array();
			$item['PRODUCT_ID'] = $obj->getProductId();
			$item['QUANTITY'] = $obj->getQuantity();
			$item['CURRENCY'] = $obj->getCurrency();
			$item['PRICE'] = $obj->getPrice();
			$item['BASE_PRICE'] = $obj->getBasePrice();
			$item['SUM_PRICE'] = $item['PRICE'] * $item['QUANTITY'];
			$item['SUM_BASE_PRICE'] = $item['BASE_PRICE'] * $item['QUANTITY'];
			$item['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($item['PRICE'], $item['CURRENCY']);
			$item['FORMATTED_BASE_PRICE'] = CCurrencyLang::CurrencyFormat($item['BASE_PRICE'], $item['CURRENCY']);
			$item['SUM_FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($item['SUM_PRICE'], $item['CURRENCY']);
			$item['SUM_FORMATTED_BASE_PRICE'] = CCurrencyLang::CurrencyFormat($item['SUM_BASE_PRICE'], $item['CURRENCY']);

			// Получение свойств продукта
			$item["PROPS"] = $obj->getPropertyCollection()->getPropertyValues();

			// Получение размеров продукта
			$product = \CCatalogProduct::GetByID($item['PRODUCT_ID']);
			$item['WEIGHT'] = $product['WEIGHT'];
			$item['WIDTH'] = $product['WIDTH'];
			$item['LENGTH'] = $product['LENGTH'];
			$item['HEIGHT'] = $product['HEIGHT'];
			$item['STOCK_QUANTITY'] = $product['QUANTITY'];
			$item['STOCK_QUANTITY_RESERVED'] = $product['QUANTITY_RESERVED'];

			// Получение IBLOCK_ID элемента с которым связан продукт
			$db_iblock_list = CIBlockElement::GetById($item['PRODUCT_ID']);
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
			$db_iblock_list = CIBlockElement::GetList(
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 */
	public function getBasketInfo(): array
	{
		if (!Loader::includeModule('sale')
			|| !Loader::includeModule('iblock')
		) {
			die();
		}

		$info = array();

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		// <DISCOUNTS> : apply
		$discounts_context = new Discount\Context\Fuser(Fuser::getId());
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		if ($discounts !== null) {
			$result = $discounts->calculate()->getData();
			$basket->applyDiscount($result['BASKET_ITEMS']);
		}
		// </DISCOUNTS>

		$info['PRICE'] = $basket->getPrice();
		$info['PRICE_WITHOUT_DISCOUNTS'] = $basket->getBasePrice();
		$info['WEIGHT'] = $basket->getWeight();
		$info['VAT_RATE'] = $basket->getVatRate();
		$info['VAT_SUM'] = $basket->getVatSum();
		$info['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($info['PRICE'], CCurrency::GetBaseCurrency());
		$info['FORMATTED_PRICE_WITHOUT_DISCOUNTS'] = CCurrencyLang::CurrencyFormat($info['PRICE_WITHOUT_DISCOUNTS'], CCurrency::GetBaseCurrency());
		$info['ITEMS_QUANTITY'] = $basket->getQuantityList();
		$info['QUANTITY'] = count($info['ITEMS_QUANTITY']);

		return $info;
	}

	/**
	 * @param $price
	 * @param null $currency
	 * @return array
	 * @throws Main\LoaderException
	 */
	public function getCurrencyFormat($price, $currency = null): array
	{
		if (!$price || !Loader::includeModule('sale')) {
			die();
		}

		$msg = array();
		$msg['status'] = false;

		if (!$currency) {
			$currency = CCurrency::GetBaseCurrency();
		}

		$msg['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($price, $currency);
		$msg['status'] = true;

		return $msg;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 */
	public function getOrderFormGroups(): array
	{
		if (!Loader::includeModule('sale')) {
			die();
		}

		// <PROPS>
		$props = array();
		$db_list = \CSaleOrderProps::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y'], false, false, ['ID', 'CODE', 'PROPS_GROUP_ID', 'NAME', 'REQUIED', 'TYPE']);
		while ($db_el = $db_list->GetNext()) {
			$props[] = $db_el;
		}
		unset($db_list);
		// </PROPS>

		// <DELIVERY>
		$delivery = Delivery\Services\Manager::getActiveList();

		foreach ($delivery as $service) {
			if ($service['CLASS_NAME'] === '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService') {
				continue;
			}
			$service['PROPS_GROUP_ID'] = 'DELIVERY';
			$service['PRICE'] = $service['CONFIG']['MAIN']['PRICE'];
			$service['LOGOTIP'] = \CFile::ResizeImageGet($service['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$props[] = $service;
		}

		$delivery_group = ['ID' => 'DELIVERY', 'NAME' => 'DELIVERY', 'SORT' => '200'];
		// </DELIVERY>

		// <PAYMENT>
		$payment = array();
		$db_list = Manager::getList(
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

		$payment_group = ['ID' => 'PAYMENT', 'NAME' => 'PAYMENT', 'SORT' => '100'];
		// </PAYMENT>

		// <GROUPS>
		$groups = array();
		$db_list = \CSaleOrderPropsGroup::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y', '!ID' => $GLOBALS['ION']['DENY_GROUPS_IDS']]);
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
				if ($prop['PROPS_GROUP_ID'] === $group['ID']) {
					$group['PROPS'][] = $prop;
				}
			}
			if (!$group['PROPS']) {
				unset($groups[$key]);
			}
		}
		sort($groups);
		usort($groups, static function ($a, $b) {
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
	 * @return int
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function createOrder($pay_system_id, $delivery_service_id, $person_type_id, $values): int
	{
		if (!$pay_system_id
			|| !$person_type_id
			|| !$values
			|| !$delivery_service_id
			|| !Loader::includeModule('sale')
		) {
			die();
		}

		// <SITE>
		$site_id = $this->context->getSite();
		// </SITE>

		// <USER>
		$user_id = \CUser::GetID();
		if ($user_id === null) {
			$user_id = \CSaleUser::GetAnonymousUserID();
		}
		// </USER>

		$allowed_fields = ['NAME', 'LASTNAME', 'EMAIL', 'PHONE', 'COMMENT'];
		if (is_array($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'])) {
			$allowed_fields = array_merge($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'], $allowed_fields);
		}

		$order = Order::create($site_id, $user_id);

		$order->setPersonTypeId($person_type_id);

		$order->setField('USER_DESCRIPTION', $values['USER_DESCRIPTION']);

		// <PROPS>
		$propertyCollection = $order->getPropertyCollection();
		foreach ($propertyCollection as $el) {
			if ($values[$el->getField('CODE')] && in_array($el->getField('CODE'), $allowed_fields)) {
				$el->setValue($values[$el->getField('CODE')]);
			}
		}
		// </PROPS>

		// <BASKET>
		$basketLoad = Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), $site_id);
		$basketItems = $basketLoad->getOrderableItems();
		$order->setBasket($basketItems);
		// </BASKET>

		// <DELIVERY>
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem(
			Delivery\Services\Manager::getObjectById($delivery_service_id)
		);
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		foreach ($basketItems as $basketItem) {
			$item = $shipmentItemCollection->createItem($basketItem);
			$item->setQuantity($basketItem->getQuantity());
		}
		// </DELIVERY>

		// <PAYMENT>
		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem(
			Manager::getObjectById($pay_system_id)
		);
		$payment->setField('SUM', $order->getPrice());
		$payment->setField('CURRENCY', $order->getCurrency());
		// </PAYMENT>

		if ($GLOBALS['ION']['MAKE_ORDER_HANDLER'] instanceof Closure) {
			$GLOBALS['ION']['MAKE_ORDER_HANDLER']($order);
		}

		$order->save();
		return $order->GetId();
	}

	/**
	 * @param $name
	 * @param int $page
	 * @param int $page_size
	 * @return array
	 * @throws Main\LoaderException
	 */
	public function searchItemsByName($name, $page = 1, $page_size = 10): array
	{
		if ($GLOBALS['ION']['SEARCH_IBLOCK_ID'] === null
			|| $name === null
			|| $page === null
			|| $page_size === null
			|| !Loader::includeModule('iblock')
		) {
			die();
		}

		$data = array(
			'ITEMS' => array(),
			'COUNT' => '0'
		);

		$allowed_fields_iblock = array(
			'ID',
			'IBLOCK_ID',
			'NAME',
			'PREVIEW_PICTURE',
			'DETAIL_PAGE_URL',
			'PREVIEW_TEXT'
		);
		if (count($GLOBALS['ION']['SEARCH_ALLOWED_FIELDS_IBLOCK']) > 0) {
			$allowed_fields_iblock = array_merge(
				$GLOBALS['ION']['SEARCH_ALLOWED_FIELDS_IBLOCK'],
				$allowed_fields_iblock
			);
		}

		$db_list = CIBlockElement::GetList(
			array(
				'SORT' => 'ASC',
				'ID' => 'ASC'
			),
			array(
				'IBLOCK_ID' => $GLOBALS['ION']['SEARCH_IBLOCK_ID'],
				'%NAME' => $name,
				'ACTIVE' => 'Y'
			),
			false,
			array(
				'nPageSize' => $page_size,
				'iNumPage' => $page
			),
			$allowed_fields_iblock
		);
		while ($db_el = $db_list->GetNext()) {
			$db_el['PREVIEW_PICTURE'] = \CFile::ResizeImageGet($db_el["PREVIEW_PICTURE"], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$data['ITEMS'][] = $db_el;
		}
		unset($db_list);

		$data['COUNT'] = CIBlockElement::GetList(
			array(
				'SORT' => 'ASC',
				'ID' => 'ASC'
			),
			array(
				'IBLOCK_ID' => $GLOBALS['ION']['SEARCH_IBLOCK_ID'],
				'%NAME' => $name
			),
			array()
		);

		return $data;
	}

	/**
	 * @param int $count
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getViewedProducts($count = 10): array
	{
		if (!Loader::includeModule('catalog')) {
			die();
		}

		$products = array();

		$db_list = CatalogViewedProductTable::getList(
			array(
				'limit' => $count,
				'select' => array('*'),
				'filter' => array(
					'FUSER_ID' => Fuser::getId(),
					'SITE_ID' => $this->context->getSite()
				),
				'order' => array('DATE_VISIT' => 'DESC')
			)
		);
		while ($db_el = $db_list->fetch()) {
			$products[] = $db_el;
		}

		return $products;
	}

	/**
	 * @param $product_id
	 * @param $element_id
	 * @param int $view_count
	 * @return Main\ORM\Data\AddResult|Main\ORM\Data\UpdateResult
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addProductViewCount($product_id, $element_id, $view_count = 1)
	{
		if ($product_id === null
			|| $element_id === null
			|| $view_count === null
			|| !Loader::includeModule('catalog')
		) {
			die();
		}

		$result = null;

		$db_list = CatalogViewedProductTable::getList(
			array(
				'limit' => '1',
				'select' => array('*'),
				'filter' => array(
					'PRODUCT_ID' => $product_id,
					'ELEMENT_ID' => $element_id,
					'FUSER_ID' => Fuser::getId(),
					'SITE_ID' => $this->context->getSite()
				)
			)
		);
		if ($db_el = $db_list->fetch()) {
			$result = CatalogViewedProductTable::update(
				$db_el['ID'],
				array(
					'VIEW_COUNT' => $db_el['VIEW_COUNT'] + $view_count,
					'DATE_VISIT' => DateTime::createFromPhp(new \DateTime()),
				)
			);
		} else {
			$result = CatalogViewedProductTable::add(
				array(
					'PRODUCT_ID' => $product_id,
					'ELEMENT_ID' => $element_id,
					'VIEW_COUNT' => $view_count,
					'FUSER_ID' => Fuser::getId(),
					'SITE_ID' => $this->context->getSite()
				)
			);
		}

		return $result;
	}

	/**
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public function removeProductsFromBasket(): bool
	{
		if (!Loader::includeModule('sale')) {
			die();
		}
		if (!Loader::includeModule('catalog')) {
			die();
		}

		return \CSaleBasket::DeleteAll(Fuser::getId());
	}
}
