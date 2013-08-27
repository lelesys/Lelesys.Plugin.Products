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

use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the value for property for specific language
 *
 * @api
 */
class NodePropertyViewHelper extends AbstractViewHelper {

	/**
	 * Returns the node property value for current locale
	 *
	 * @param TYPO3\TYPO3CR\Domain\Model\NodeInterface $node The node
	 * @param strind $property The node property
	 * @param string $locale The locale
	 * @return mixed Value of the given property
	 */
	public function render(NodeInterface $node, $property, $locale = NULL) {
		if (is_object($node) === TRUE) {
			if ($locale === NULL) {
				$contextProperties = $node->getContext()->getProperties();
				if (!empty($contextProperties['locale'])) {
					$locale = $contextProperties['locale']->getLanguage();
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

}

?>