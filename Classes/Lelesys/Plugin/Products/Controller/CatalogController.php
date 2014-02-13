<?php
namespace Lelesys\Plugin\Products\Controller;

/*                                                                         *
 * This script belongs to the package "Lelesys.Plugin.Products".           *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the GNU Lesser General Public License, either version 3    *
 * of the License, or (at your option) any later version.                  *
 *                                                                         */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Flow\Security\Exception\AccessDeniedException;

/**
 * Catalog controller for the Lelesys.Plugin.Products
 *
 * @Flow\Scope("singleton")
 */
class CatalogController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * Inject NodeTypeManager
	 *
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * Inject ResourceManager
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * Inject CatalogService
	 *
	 * @Flow\Inject
	 * @var \Lelesys\Plugin\Products\Service\CatalogService
	 */
	protected $catalogService;

	/**
	 * Display s the list of categories or products.
	 *
	 * @return void|string
	 */
	public function indexAction() {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		if ($documentNode !== NULL) {
			$this->view->assign('pageNode', $documentNode);
			if ($documentNode->hasChildNodes('Lelesys.Plugin.Products:Category')) {
				$this->view->assign('itemNodes', 'Lelesys.Plugin.Products:Category');
			}
			if ($documentNode->hasChildNodes('Lelesys.Plugin.Products:Product')) {
				$this->view->assign('itemNodes', 'Lelesys.Plugin.Products:Product');
			}
		} else {
			return 'Error: The Plugin cannot determine the current document node.';
		}
	}

	/**
	 * Admin plugin for creation of product and categories.
	 *
	 * @return void|string
	 */
	public function adminAction() {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		if ($documentNode !== NULL) {
			$this->view->assign('pageNode', $documentNode);
			if ($documentNode->hasChildNodes('Lelesys.Plugin.Products:Category')) {
				$this->view->assign('itemNodes', 'Lelesys.Plugin.Products:Category');
			}
			if ($documentNode->hasChildNodes('Lelesys.Plugin.Products:Product')) {
				$this->view->assign('itemNodes', 'Lelesys.Plugin.Products:Product');
			}
		} else {
			return 'Error: The Plugin cannot determine the current document node.';
		}
	}

	/**
	 * Creates a new category or product.
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $pageNode
	 * @param string $nodeType The NodeType
	 * @param string $uri The URI
	 * @return void
	 */
	public function createAction(NodeInterface $pageNode = NULL, $nodeType = NULL, $uri = NULL) {
		if ($pageNode === NULL) {
			$pageNode = $this->request->getInternalArgument('__documentNode');
		}

		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($nodeType));
		switch ($nodeType) {
			case 'Lelesys.Plugin.Products:Category':
				$nodeTemplate->setProperty('title', 'new category');
				$slug = uniqid('category');
				break;
			case'Lelesys.Plugin.Products:Product':
				$nodeTemplate->setProperty('title', 'new product');
				$slug = uniqid('product');
				break;
		}
		$pageNode->createNodeFromTemplate($nodeTemplate, $slug);

		if ($uri === NULL) {
			$uri = $this->getRedirectUri($pageNode);
		}
		$this->redirectToUri($uri);
	}

	/**
	 * Displays form to upload files.
	 *
	 * @return void
	 */
	public function fileUploadAction() {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		$filesNode = $documentNode->getNode('files');
		if ($filesNode !== NULL && $filesNode->hasChildNodes('Lelesys.Plugin.Products:File')) {
			$this->view->assign('fileNodes', $filesNode->getChildNodes('Lelesys.Plugin.Products:File'));
		}
	}

	/**
	 * Creates a file node for product.
	 *
	 * @param array $fileUpload Array of files to be uploaded
	 * @return void
	 */
	public function uploadFilesAction(array $fileUpload) {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		$fileResource = $this->resourceManager->importUploadedResource($fileUpload);
		if ($fileResource) {
			$this->persistenceManager->add($fileResource);
			$fileNode = $documentNode->getNode('files')->createNode(uniqid('file'), $this->nodeTypeManager->getNodeType('Lelesys.Plugin.Products:File'));
			$fileNode->setProperty('file', $fileResource);
		}
		$this->redirectToUri($this->getRedirectUri($documentNode));
	}

	/**
	 * Downloads the product file.
	 *
	 * @param array $file Array of files
	 * @return void
	 */
	public function downloadFilesAction(array $file) {
		$this->catalogService->downaloadFile($file);
	}

	/**
	 * Displays list of related products and enables
	 * addition of related products in backend.
	 *
	 * @return void
	 */
	public function relatedProductsAction() {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		$hasRelatedproducts = $documentNode->hasChildNodes('Lelesys.Plugin.Products:RelatedProduct');
		if ($hasRelatedproducts) {
			$relatedItems = NULL;
			$relatedProductNodes = $documentNode->getChildNodes('Lelesys.Plugin.Products:RelatedProduct');
			foreach ($relatedProductNodes as $relatedProductNode) {
				$path = $relatedProductNode->getProperty('path');
				$relatedProduct = $documentNode->getNode($path);
				if ($relatedProduct) {
					$relatedItems[] = array($relatedProduct, $relatedProductNode);
					$ignoreNodes[] = $path;
				} else {
					// TO DO: Delete RelatedProudct node if Product node does not exist
					// does not work cause of GET method
					$relatedProductNode->remove();
				}
			}
			$this->view->assign('relatedItems', $relatedItems);
		}
		if ($this->catalogService->hasAccessToResource('TYPO3_Neos_Backend_GeneralAccess')) {
			$products = NULL;
			$ignoreNodes[] = $documentNode->getPath();
			$productNodes = $this->catalogService->findByNodeTypeAndCurrentSite('Lelesys.Plugin.Products:Product', $documentNode->getContext()->getCurrentSiteNode()->getPath(), $ignoreNodes);
			foreach ($productNodes as $productNode) {
				$products[] = $documentNode->getNode($productNode->getpath());
			}
			$this->view->assign('products', $products);
		}
	}

	/**
	 * Creates a related product node for product.
	 *
	 * @param array $relatedProducts The paths of related products nodes
	 * @return void
	 */
	public function relateAction(array $relatedProducts) {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		foreach ($relatedProducts as $relatedProduct) {
			$relatedProductNode = $documentNode->createNode(uniqid('RelatedProduct'), $this->nodeTypeManager->getNodeType('Lelesys.Plugin.Products:RelatedProduct'));
			$relatedProductNode->setProperty('path', $relatedProduct);
		}
		$this->redirectToUri($this->getRedirectUri($documentNode));
	}

	/**
	 * Return the redirect uri
	 *
	 * @param TYPO3\TYPO3CR\Domain\Model\NodeInterface $documentNode The document node
	 * @return string
	 */
	protected function getRedirectUri($documentNode) {
		$mainRequest = $this->request->getMainRequest();
		$mainUriBuilder = new UriBuilder();
		$mainUriBuilder->setRequest($mainRequest);
		$mainUriBuilder->setFormat('html');
		$uri = $mainUriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('show', array('node' => $documentNode));
		return $uri;
	}

	/**
	 * Deletes the node.
	 *
	 * @param array $nodes The nodes to be deleted
	 */
	public function deleteAction($nodes) {
		$documentNode = $this->request->getInternalArgument('__documentNode');
		foreach ($nodes as $node) {
			$documentNode->getNode($node)->remove();
		}
		$this->redirectToUri($this->getRedirectUri($documentNode));
	}
}
?>