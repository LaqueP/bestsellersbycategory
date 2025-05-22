<?php
/**
 * 2007-2025 PrestaShop.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BestSellersByCategory extends Module
{
    public function __construct()
    {
        $this->name = 'bestsellersbycategory';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Nest Dream';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bestsellers by Category');
        $this->description = $this->l('Muestra los productos más vendidos por categoría en la vista de categoría.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeaderCategory')
            && Configuration::updateValue('BSBC_NBR', 8);
    }

    public function uninstall()
    {
        return parent::uninstall() && Configuration::deleteByName('BSBC_NBR');
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitBSBC')) {
            Configuration::updateValue('BSBC_NBR', (int) Tools::getValue('BSBC_NBR'));
            return $this->displayConfirmation($this->l('Configuración actualizada.'));
        }

        return '
        <form method="post">
            <label>' . $this->l('Número de productos a mostrar') . '</label>
            <input type="text" name="BSBC_NBR" value="' . (int) Configuration::get('BSBC_NBR') . '">
            <input type="submit" name="submitBSBC" value="' . $this->l('Guardar') . '" class="button">
        </form>';
    }

    public function hookDisplayHeaderCategory($params)
    {
        $category = $this->context->controller->getCategory();

        if (!Validate::isLoadedObject($category)) {
            return '';
        }

        $products = ProductSale::getBestSalesLight(
            (int) $this->context->language->id,
            0,
            (int) Configuration::get('BSBC_NBR'),
            null,
            (int) $category->id,
            (int) $this->context->shop->id
        );

        if (empty($products)) {
            return '';
        }

        $link = $this->context->link;

        foreach ($products as &$product) {
            $product['link'] = $link->getProductLink($product['id_product']);
            $product['image'] = $link->getImageLink($product['link_rewrite'], $product['id_image'], 'home_default');
        }

        $this->context->smarty->assign([
            'products' => $products,
            'title' => $this->l('Top ventas en esta categoría'),
            'hookName' => 'category',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/best_sellers_carousel.tpl');
    }
}
