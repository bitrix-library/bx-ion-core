<?php

namespace Ion;

use Exception;
use Closure;
use CCurrency;
use CCurrencyLang;
use CIBlockElement;
use CSaleBasket;
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
 * Class Ion
 *
 * @author https://github.com/amensum
 * @package Ion
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
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
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

	/**
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private function loadModules(): bool
	{
		$iblockInc = Loader::includeModule('iblock');
		$catalogInc = Loader::includeModule('catalog');
		$saleInc = Loader::includeModule('sale');

		return $iblockInc && $catalogInc && $saleInc;
	}

	public static function connectOnProlog(): void
	{
		$instance = self::getInstance();
		Asset::getInstance()->addJs($instance->module_relative_path . '/assets/js/ion.js');
		Asset::getInstance()->addCss($instance->module_relative_path . '/assets/css/ion.css');
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
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function registerRequestHandlers(): void
	{
		if ($this->request['ion'] !== null) {
			$GLOBALS['APPLICATION']->RestartBuffer();

			if (!$this->loadModules()) {
				return;
			}

			$response = null;

			switch ($this->request['ion']) {
				case 'get_ion_status':
					$response = $this->getIonStatus();
					break;

				case 'get_closure':
					$id = (int)$this->request['id'];
					$response = $this->getClosure($id);
					break;

				case 'set_product_in_basket':
					$id = (int)$this->request['product_id'];
					$quantity = (string)$this->request['quantity'];
					$props = (array)$this->request['props'];
					$response = $this->setProductInBasket(
						$id,
						$quantity,
						$props
					);
					break;

				case 'remove_product_from_basket':
					$product_id = (int)$this->request['product_id'];
					$props = (array)$this->request['props'];
					$response = $this->removeProductFromBasket($product_id, $props);
					break;

				case 'get_items_from_basket':
					$response = $this->getItemsFromBasket();
					break;

				case 'get_basket_info':
					$response = $this->getBasketInfo();
					break;

				case 'get_currency_format':
					$price = (float)$this->request['price'];
					$response = $this->getCurrencyFormat($price);
					break;

				case 'get_order_form_groups':
					$response = $this->getOrderFormGroups();
					break;

				case 'order_make_order':
					$delivery_service_id = (int)$this->request["delivery_service_id"];
					$pay_system_id = (int)$this->request["pay_system_id"];
					$person_type_id = (int)$this->request["person_type_id"];
					$values = json_decode($this->request["values"], true);
					$response = $this->createOrder($pay_system_id, $delivery_service_id, $person_type_id, $values);
					break;

				case 'search_items_by_name':
					$response = $this->searchItemsByName($this->request["name"], $this->request["page"]);
					break;
			}

			echo Json::encode($response);
		}
	}

	/**
	 * @return array
	 */
	public function getIonStatus(): array
	{
		return [
			'status' => true,
			'result' => null
		];
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getClosure(int $id): array
	{
		if ($GLOBALS['ION']['CLOSURES'][$id] instanceof Closure) {
			return [
				'status' => true,
				'result' => $GLOBALS['ION']['CLOSURES'][$id]()
			];
		}

		return [
			'status' => false,
			'result' => null
		];
	}

    /**
     * @param int $product_id
     * @param string $quantity
     * @param array $props
     * @return array
     */
	public function setProductInBasket(int $product_id, string $quantity, array $props): array
	{
        try {
            $site = $this->context->getSite();

            $basket = Basket::loadItemsForFUser(Fuser::getId(), $site);

            $new_quantity = $quantity;

            if ($basketItem = $basket->getExistsItem('catalog', $product_id, $props)) {
                if ($new_quantity[0] === '>') {
                    $new_quantity = $basketItem->getQuantity() + substr($new_quantity, 1);
                }

                if ($new_quantity[0] === '<') {
                    $new_quantity = $basketItem->getQuantity() - substr($new_quantity, 1);
                }

                if ($new_quantity < 1) {
                    $new_quantity = 1;
                }

                // Обновление товара в корзине
                $basketItem->setField('QUANTITY', $new_quantity);
            } else {
                if ($new_quantity[0] === '>') {
                    $new_quantity = substr($new_quantity, 1);
                }

                if ($new_quantity[0] === '<') {
                    $new_quantity = -substr($new_quantity, 1);
                }

                if ($new_quantity < 1) {
                    $new_quantity = 1;
                }

                // Добавление товара в корзину
                $basketItem = $basket->createItem('catalog', $product_id);
                $basketItem->setFields([
                    'QUANTITY' => $new_quantity,
                    'CURRENCY' => CurrencyManager::getBaseCurrency(),
                    'LID' => $site,
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
            }

            $basket->save();

            $basketItem = $basket->getExistsItem('catalog', $product_id, $props);

            if ((bool)$basketItem && (float)$basketItem->getQuantity() === (float)$new_quantity) {
                return [
                    'status' => true,
                    'result' => [
                        'message' => 'Товар добавлен в корзину',
                        'data' => $basketItem->getQuantity()
                    ],
                ];
            }

            return [
                'status' => false,
                'result' => [
                    'message' => 'Не удалось изменить количество или добавить товар в корзине',
                ],
            ];
        } catch (Exception $exception) {
            return [
                'status' => false,
                'result' => [
                    'message' => 'Что-то пошло не так',
                    'exception' => $exception->getMessage()
                ]
            ];
        }
    }

	/**
	 * @param int $product_id
	 * @param array $props
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function removeProductFromBasket(int $product_id, array $props = []): array
	{
		if (!$product_id) {
			return [
				'status' => false,
				'result' => null
			];
		}

		$status = false;

		if ($props === null) {
			$props = [];
		}

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		if ($basketItem = $basket->getExistsItem('catalog', $product_id, $props)) {

			$basketItem->delete();
			$basket->save();

			$status = true;
		}

		return [
			'status' => true,
			'result' => $status
		];
	}

	/**
	 * @param null $fuser
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\NotImplementedException
	 */
	public function getItemsFromBasket($fuser = null): array
	{
		if ($fuser === null) {
			$fuser = Fuser::getId();
		}

		$items = array();

		$basket = Basket::loadItemsForFUser($fuser, $this->context->getSite());

		/*
		 * Discounts
		 */
		$discounts_context = new Discount\Context\Fuser($fuser);
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		if ($discounts !== null) {
			$result = $discounts->calculate()->getData();
			$basket->applyDiscount($result['BASKET_ITEMS']);
		}

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

		return [
			'status' => true,
			'result' => $items
		];
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\InvalidOperationException
	 * @throws Main\NotImplementedException
	 */
	public function getBasketInfo(): array
	{
		$info = [];

		$basket = Basket::loadItemsForFUser(Fuser::getId(), $this->context->getSite());

		/*
		 * Discounts
		 */
		$discounts_context = new Discount\Context\Fuser(Fuser::getId());
		$discounts = Discount::buildFromBasket($basket, $discounts_context);
		if ($discounts !== null) {
			$result = $discounts->calculate()->getData();
			$basket->applyDiscount($result['BASKET_ITEMS']);
		}

		$info['PRICE'] = $basket->getPrice();
		$info['PRICE_WITHOUT_DISCOUNTS'] = $basket->getBasePrice();
		$info['WEIGHT'] = $basket->getWeight();
		$info['VAT_RATE'] = $basket->getVatRate();
		$info['VAT_SUM'] = $basket->getVatSum();
		$info['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($info['PRICE'], CCurrency::GetBaseCurrency());
		$info['FORMATTED_PRICE_WITHOUT_DISCOUNTS'] = CCurrencyLang::CurrencyFormat($info['PRICE_WITHOUT_DISCOUNTS'], CCurrency::GetBaseCurrency());
		$info['ITEMS_QUANTITY'] = $basket->getQuantityList();
		$info['QUANTITY'] = count($info['ITEMS_QUANTITY']);

		return [
			'status' => true,
			'result' => $info
		];
	}

	/**
	 * @param $price
	 * @param null $currency
	 * @return array
	 */
	public function getCurrencyFormat($price, $currency = null): array
	{
		if (!$price) {
			return [
				'status' => false,
				'result' => null
			];
		}

		$msg = [];
		$msg['status'] = false;

		if (!$currency) {
			$currency = CCurrency::GetBaseCurrency();
		}

		$msg['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($price, $currency);
		$msg['status'] = true;

		return [
			'status' => true,
			'result' => $msg
		];
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function getOrderFormGroups(): array
	{
		/*
		 * Props
		 */
		$props = array();
		$db_list = \CSaleOrderProps::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['ACTIVE' => 'Y'], false, false, ['ID', 'CODE', 'PROPS_GROUP_ID', 'NAME', 'REQUIED', 'TYPE']);
		while ($db_el = $db_list->GetNext()) {
			$props[] = $db_el;
		}
		unset($db_list);

		/*
		 * Delivery
		 */
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

		/*
		 * Payment
		 */
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

		/*
		 * Groups
		 */
		$groups = array();
		$db_list = \CSaleOrderPropsGroup::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y', '!ID' => $GLOBALS['ION']['DENY_GROUPS_IDS']]);
		while ($db_el = $db_list->GetNext()) {
			$groups[] = $db_el;
		}
		unset($db_list);
		$groups[] = $delivery_group;
		$groups[] = $payment_group;

		/*
		 * Props to Groups
		 */
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

		return [
			'status' => true,
			'result' => $groups
		];
	}

	/**
	 * @param int $pay_system_id
	 * @param int $delivery_service_id
	 * @param int $person_type_id
	 * @param array $values
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function createOrder(int $pay_system_id, int $delivery_service_id, int $person_type_id, array $values):
	array
	{
		if (!$pay_system_id
			|| !$person_type_id
			|| !$values
			|| !$delivery_service_id
		) {
			return [
				'status' => false,
				'result' => null
			];
		}

		/*
		 * Site
		 */
		$site_id = $this->context->getSite();

		/*
		 * User
		 */
		$user_id = \CUser::GetID();
		if ($user_id === null) {
			$user_id = \CSaleUser::GetAnonymousUserID();
		}

		$allowed_fields = ['NAME', 'LASTNAME', 'EMAIL', 'PHONE', 'COMMENT'];
		if (is_array($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'])) {
			$allowed_fields = array_merge($GLOBALS['ION']['ORDER_ALLOWED_FIELDS'], $allowed_fields);
		}

		$order = Order::create($site_id, $user_id);

		$order->setPersonTypeId($person_type_id);

		$order->setField('USER_DESCRIPTION', $values['USER_DESCRIPTION']);

		/*
		 * Props
		 */
		$propertyCollection = $order->getPropertyCollection();
		foreach ($propertyCollection as $el) {
			if ($values[$el->getField('CODE')] && in_array($el->getField('CODE'), $allowed_fields)) {
				$el->setValue($values[$el->getField('CODE')]);
			}
		}

		/*
		 * Basket
		 */
		$basketLoad = Sale\Basket::loadItemsForFUser(CSaleBasket::GetBasketUserID(), $site_id);
		$basketItems = $basketLoad->getOrderableItems();
		$order->setBasket($basketItems);

		/*
		 * Delivery
		 */
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem(
			Delivery\Services\Manager::getObjectById($delivery_service_id)
		);
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		foreach ($basketItems as $basketItem) {
			$item = $shipmentItemCollection->createItem($basketItem);
			$item->setQuantity($basketItem->getQuantity());
		}

		/*
		 * Payment
		 */
		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem(
			Manager::getObjectById($pay_system_id)
		);
		$payment->setField('SUM', $order->getPrice());
		$payment->setField('CURRENCY', $order->getCurrency());

		if ($GLOBALS['ION']['MAKE_ORDER_HANDLER'] instanceof Closure) {
			$GLOBALS['ION']['MAKE_ORDER_HANDLER']($order);
		}

		$order->save();

		return [
			'status' => true,
			'result' => $order->GetId()
		];
	}

	/**
	 * @param string $name
	 * @param int $page
	 * @param int $page_size
	 * @return array
	 */
	public function searchItemsByName(string $name, int $page = 1, int $page_size = 10): array
	{
		if ($GLOBALS['ION']['SEARCH_IBLOCK_ID'] === null
			|| $name === null
			|| $page === null
			|| $page_size === null
		) {
			return [
				'status' => false,
				'result' => null
			];
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

		return [
			'status' => true,
			'result' => $data
		];
	}

	/**
	 * @param int $count
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getViewedProducts(int $count = 10): array
	{
		$products = [];

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

		return [
			'status' => true,
			'result' => $products
		];
	}

	/**
	 * @param int $product_id
	 * @param int $element_id
	 * @param int $view_count
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addProductViewCount(int $product_id, int $element_id, int $view_count = 1): array
	{
		if ($product_id === null
			|| $element_id === null
			|| $view_count === null
		) {
			return [
				'status' => false,
				'result' => null
			];
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

		return [
			'status' => true,
			'result' => $result
		];
	}

	/**
	 * @return array
	 */
	public function removeProductsFromBasket(): array
	{
		return [
			'status' => true,
			'result' => CSaleBasket::DeleteAll(Fuser::getId())
		];
	}
}
