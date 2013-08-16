<?php

namespace Lelesys\Plugin\Products\Service;

/*                                                                         *
 * This script belongs to the package "Lelesys.Plugin.Products".           *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the GNU Lesser General Public License, either version 3    *
 * of the License, or (at your option) any later version.                  *
 *                                                                         */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Exception\AccessDeniedException;

/**
 * Catalog service for the Lelesys.Plugin.Products
 *
 * @Flow\Scope("singleton")
 */
class CatalogService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * Returns node by the specified node type
	 *
	 * @param string $nodeType Type of node to be searched.
	 * @param string $currentSiteNodePath Current site node path.
	 * @param array  $ignoreNodes Array of node paths which need to be ignored.
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findByNodeTypeAndCurrentSite($nodeType, $currentSiteNodePath, $ignoreNodes = NULL) {
		if ($ignoreNodes === NULL) {
			$ignoreNodes[] = '';
		}
		$query = $this->nodeDataRepository->createQuery();
		$constraints = array(
			$query->like('path', $currentSiteNodePath . '%'),
			$query->equals('nodeType', $nodeType),
			$query->logicalNot($query->in('path', $ignoreNodes))
		);
		return $query->matching($query->logicalAnd($constraints))->execute();
	}

	/**
	 * Check if we currently have access to the given resource
	 *
	 * @param string $resource The resource to check
	 * @return boolean TRUE if we currently have access to the given resource
	 */
	public function hasAccessToResource($resource) {
		try {
			$this->accessDecisionManager->decideOnResource($resource);
		} catch (AccessDeniedException $e) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Downloads the file
	 *
	 * @param array $file
	 * @return void
	 */
	public function downaloadFile(array $file) {
		$fileResource = $this->propertyMapper->convert($file['__identity'], '\TYPO3\Flow\Resource\Resource');
		$filePath = $this->resourceManager->getPersistentResourcesStorageBaseUri() . $fileResource->getResourcePointer()->getHash();
		$fileMimeType = mime_content_type($filePath);
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $fileMimeType);
		header('Content-Disposition: attachment; filename=' . basename($fileResource->getFilename()));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filePath));
		ob_clean();
		flush();
		readfile($filePath);
	}

}

?>