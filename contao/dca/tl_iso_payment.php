<?php
/*
 * @author  Henry Lamorski <henry.lamorski@mailbox.org>
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['PayPalFloat'] = str_replace(
    '{config_legend},new_order_status',
    '{config_legend},uos_paypalfloat,new_order_status',
    $GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['paypal']
);


/**
 * Add fields to tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['uos_paypalfloat'] = array
(
    'label'                     => array('attribute used for uos'),
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Model\Payment\PayPalFloat', 'getDcaOptions'),
    'eval'                      => array('tl_class'=>'clr'),
    'sql'                       => 'varchar(255)',
);
