<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    LaqueP
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Entity\Product;
use PrestaShop\PrestaShop\Adapter\Entity\Category;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class BestSellersByCategory extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'bestsellersbycategory';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'LaqueP';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('Best Sellers by Category', [], 'Modules.Bestsellersbycategory.Admin');
        $this->description = $this->trans('Displays best-selling products by category and on product page.', [], 'Modules.Bestsellersbycategory.Admin');

        $this->templateFile = 'module:' . $this->name . '/views/templates/hook/best_sellers_carousel.tpl';
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        Configuration::updateValue('BSBC_NBR_PRODUCTS', 5);

        $titles = [];
        foreach (Language::getLanguages(true) as $lang) {
            $titles[$lang['id_lang']] = $this->trans('Popular products', [], 'Modules.Bestsellersbycategory.Admin', $lang['locale']);
        }
        Configuration::updateValue('BSBC_CAROUSEL_TITLE', $titles, true);

        return $this->registerHook('displayHeaderCategory')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function uninstall()
    {
        Configuration::deleteByName('BSBC_NBR_PRODUCTS');
        Configuration::deleteByName('BSBC_CAROUSEL_TITLE');
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBsbcConfig')) {
            $nbr = (int) Tools::getValue('BSBC_NBR_PRODUCTS');
            Configuration::updateValue('BSBC_NBR_PRODUCTS', $nbr);

            $titles = [];
            foreach (Language::getLanguages(true) as $lang) {
                $titles[$lang['id_lang']] = Tools::getValue('BSBC_CAROUSEL_TITLE_' . $lang['id_lang']);
            }
            Configuration::updateValue('BSBC_CAROUSEL_TITLE', $titles, true);

            $output .= $this->displayConfirmation(
                $this->trans('Settings updated', [], 'Admin.Notifications.Success')
            );
        }

        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->trans(
                    'BestSellersByCategory settings',
                    [],
                    'Modules.Bestsellersbycategory.Admin'
                ),
            ],
            'input' => [
                [
                    'type'  => 'text',
                    'label' => $this->trans(
                        'Carousel title',
                        [],
                        'Modules.Bestsellersbycategory.Admin'
                    ),
                    'name'  => 'BSBC_CAROUSEL_TITLE',
                    'lang'  => true,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->trans(
                        'Number of products',
                        [],
                        'Modules.Bestsellersbycategory.Admin'
                    ),
                    'name'  => 'BSBC_NBR_PRODUCTS',
                    'col'   => 2,
                ],
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Admin.Actions'),
                'name'  => 'submitBsbcConfig',
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $helper->languages = Language::getLanguages(true);
        $helper->fields_value = $this->getConfigFieldsValues();

        return $output . $helper->generateForm($fieldsForm);
    }

    public function getConfigFieldsValues()
    {
        $values = [];
        foreach (Language::getLanguages(true) as $lang) {
            $values['BSBC_CAROUSEL_TITLE'][$lang['id_lang']] = Configuration::get(
                'BSBC_CAROUSEL_TITLE',
                $lang['id_lang'],
                false
            );
        }
        $values['BSBC_NBR_PRODUCTS'] = Configuration::get('BSBC_NBR_PRODUCTS');

        return $values;
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'bsbc-style',
            'modules/' . $this->name . '/views/css/styles.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'bsbc-script',
            'modules/' . $this->name . '/views/js/main.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }

    public function hookDisplayHeaderCategory($params)
    {
        $category = $params['category'] ?? Context::getContext()->controller->getCategory();
        return $this->renderCarousel((int) $category->id, 'displayHeaderCategory', 'sales');
    }

    public function hookDisplayFooterProduct($params)
    {
        $product = $params['product'] ?? Context::getContext()->controller->getProduct();
        $html = $this->renderCarousel((int) $product->id_default_category, 'displayFooterProduct', 'sales');
        if (!$html) {
            $html = $this->renderCarousel((int) $product->id_default_category, 'displayFooterProduct', 'cart');
        }
        return $html;
    }

    protected function renderCarousel($idCat, $hookName, $metric)
    {
        $nbr = (int) Configuration::get('BSBC_NBR_PRODUCTS');
        $alias = $metric === 'sales' ? 'od' : 'cp';
        $table = $metric === 'sales' ? 'order_detail' : 'cart_product';
        $join = $metric === 'sales'
            ? 'JOIN `' . _DB_PREFIX_ . 'orders` o ON od.id_order=o.id_order AND o.valid=1'
            : '';
        $field = $metric === 'sales' ? 'od.product_quantity' : 'cp.quantity';

        $sql = 'SELECT ' . $alias . '.product_id, SUM(' . $field . ') AS total '
            . 'FROM `' . _DB_PREFIX_ . $table . '` ' . $alias . ' ' . $join . ' '
            . 'JOIN `' . _DB_PREFIX_ . 'category_product` cp2 ON cp2.id_product=' . $alias . '.product_id '
            . 'WHERE cp2.id_category=' . (int) $idCat . ' '
            . 'GROUP BY ' . $alias . '.product_id '
            . 'ORDER BY total DESC '
            . 'LIMIT ' . (int) $nbr;

        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            return '';
        }

        $idLang = $this->context->language->id;
        $products = [];
        foreach ($rows as $r) {
            $prod = new Product((int) $r['product_id'], true, $idLang);
            $products[] = Product::getProductProperties($idLang, $prod->toArray());
        }

        $title = Configuration::get('BSBC_CAROUSEL_TITLE', $idLang);
        $this->context->smarty->assign(compact('products', 'title', 'hookName'));

        return $this->fetch($this->templateFile);
    }

    public function renderWidget($hookName, array $config)
    {
        if ($hookName === 'displayHeaderCategory') {
            return $this->hookDisplayHeaderCategory($config);
        }
        if ($hookName === 'displayFooterProduct') {
            return $this->hookDisplayFooterProduct($config);
        }
        return '';
    }

    public function getWidgetVariables($hookName, array $config)
    {
        return [];
    }
}
