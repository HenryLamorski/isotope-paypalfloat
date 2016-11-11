<?php
/**
 * GeoShipping extension for Isotope eCommerce provides an shipping-method that calculates the shippingprice based on kilometers between shop-postalcode and shipping-postalcode
 *
 * Copyright (c) 2016 Henry Lamorski
 *
 * @package GeoShipping
 * @author  Henry Lamorski <henry.lamorski@mailbox.org>
 */


namespace Isotope\Model\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Isotope\Model\Payment\Paypal;


class PayPalFloat extends PayPal
{
 

    /**
     * Return the PayPal form.
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $arrData     = array();
        $fltDiscount = 0;
        $i           = 0;

        foreach ($objOrder->getItems() as $objItem) {

            // Set the active product for insert tags replacement
            if ($objItem->hasProduct()) {
                Product::setActive($objItem->getProduct());
            }

            $strConfig = '';
            $arrConfig = $objItem->getConfiguration();

            if (!empty($arrConfig)) {

                array_walk(
                    $arrConfig,
                    function(&$option) {
                        $option = $option['label'] . ': ' . (string) $option;
                    }
                );

                $strConfig = ' (' . implode(', ', $arrConfig) . ')';
            }

            $arrData['item_number_' . ++$i] = $objItem->getSku();
            $arrData['item_name_' . $i]     = \StringUtil::restoreBasicEntities(
                $objItem->getName() . $strConfig
            );

            $arrData['amount_' . $i]        = $objItem->getPrice();
            $arrData['quantity_' . $i]      = $objItem->quantity;

            if(isset($arrConfig['anzahl']))
            {
                $totalUos = $objItem->quantity*$objItem->getPrice();
                $priceUom = round($totalUos/$objItem->getConfiguration()['anzahl']->value,2);

                $arrData['amount_' . $i]        = $priceUom;
                $arrData['quantity_' . $i]      = $objItem->getConfiguration()['anzahl']->value;
            }

        }

        foreach ($objOrder->getSurcharges() as $objSurcharge) {

            if (!$objSurcharge->addToTotal) {
                continue;
            }

            // PayPal does only support one single discount item
            if ($objSurcharge->total_price < 0) {
                $fltDiscount -= $objSurcharge->total_price;
                continue;
            }

            $arrData['item_name_' . ++$i] = $objSurcharge->label;
            $arrData['amount_' . $i]      = $objSurcharge->total_price;
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_paypal');
        $objTemplate->setData($this->arrData);

        $objTemplate->id            = $this->id;
        $objTemplate->action        = ('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr');
        $objTemplate->invoice       = $objOrder->getId();
        $objTemplate->data          = array_map('specialchars', $arrData);
        $objTemplate->discount      = $fltDiscount;
        $objTemplate->address       = $objOrder->getBillingAddress();
        $objTemplate->currency      = $objOrder->getCurrency();
        $objTemplate->return        = \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder);
        $objTemplate->cancel_return = \Environment::get('base') . Checkout::generateUrlForStep('failed');
        $objTemplate->notify_url    = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->headline      = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message       = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel        = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }


}
