{*
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($products) && $products|@count > 0}
  <section class="bsbc-section">
    <h3 class="bsbc-title">{$title|escape:'html':'UTF-8'}</h3>
    <div class="owl-carousel bsbc-carousel bsbc-{$hookName|escape:'html':'UTF-8'}">
      {foreach from=$products item=product}
        <div class="item text-center">
          <a href="{$product.url|escape:'html':'UTF-8'}" class="thumbnail-container d-block">
            <img 
              class="img-fluid"
              src="{$product.cover.bySize.home_default.url|escape:'html':'UTF-8'}"
              alt="{$product.name|escape:'html':'UTF-8'}"
            >
          </a>
          <h4 class="product-title h6 mt-2 mb-1">
            <a href="{$product.url|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}">
              {$product.name|truncate:30:'...'|escape:'html':'UTF-8'}
            </a>
          </h4>
          <span class="price">{$product.price}</span>
        </div>
      {/foreach}
    </div>
  </section>
{/if}

