<?php

namespace Lelesys\Plugin\Products\ViewHelpers;

/* *
 * This script belongs to the package "Lelesys.Plugin.Products".          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 *
 * @api
 */
class ValueViewHelper extends AbstractViewHelper {

	/**
	 * Returns the node property value for current locale
	 *
	 * @param TYPO3\TYPO3CR\Domain\Model\NodeInterface $node The node
	 * @param strind $property The node property
	 * @param string $locale The locale
	 */
	public function render($node = NULL, $property = NULL, $locale = NULL) {
		if ($locale === NULL) {
			$contextProperties = $node->getContext()->getProperties();
			if (!empty($contextProperties['locale'])) {
				$locale = $contextProperties['locale'];
			}
		}
		$value = $node->getProperty($property);
		if (is_array($value) && $locale !== NULL) {
			if (!empty($value[$locale])) {
				return $value[$locale];
			}
		}
		return $value;
	}

}

?>