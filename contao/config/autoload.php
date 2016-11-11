<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Isotope',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Src
	'Isotope\Model\Payment\PayPalFloat' => 'system/modules/isotope-paypalfloat/src/Isotope/Model/Payment/PayPalFloat.php',
));
