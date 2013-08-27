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

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Strips all html tags and crops text
 *
 * @api
 */
class TeaserViewHelper extends AbstractViewHelper {

	/**
	 * Renders text without html tags and crops text if max characters is set.
	 *
	 * @param integer $maxCharacters Place where to truncate the string
	 * @param string $append What to append, if truncation happened
	 * @param string $value The input value
	 * @return string Text
	 * @api
	 */
	public function render($maxCharacters = NULL, $append = '...', $value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		$value = strip_tags($value);

		if ($maxCharacters !== NULL && strlen($value) > $maxCharacters) {
			return substr($value, 0, $maxCharacters) . $append;
		} else {
			return $value;
		}
	}
}


?>