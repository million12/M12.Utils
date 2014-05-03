<?php
namespace M12\Utils\TypoScript;

/*                                                                        *
 * This script belongs to the M12.Utils package                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;
use TYPO3\TypoScript\Exception;

/**
 * Implements WrapRemover
 *
 * Removes extra NodeType wrapper, only when it contains its default class
 * (set in wrapperClass TypoScript variable).
 *
 * All TYPO3.Neos:Content elements need extra wrapper around, used mainly
 * in Neos back-end, where all extra data-* attributes needed for inline
 * editing are added. In the 'live' workspace though, it results with extra
 * DIV around the element, with its default class (eg. typo3-neos-nodetypes-*).
 *
 * We assume here that these wrappers are redundant *unless* some extra
 * classes or other attributes were set. In such case we don't remove
 * the wrapper.
 */
class WrapRemoverImplementation extends AbstractTypoScriptObject {

	/**
	 * The string to be processed
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->tsValue('value');
	}

	/**
	 * @return NodeInterface
	 */
	public function getNode() {
		return $this->tsValue('node');
	}

	/**
	 * @return NodeInterface
	 */
	public function getContentCollectionNode() {
		$currentContext = $this->tsRuntime->getCurrentContext();
		return $currentContext['contentCollectionNode'];
	}

	/**
	 * Get current workspace name
	 *
	 * @return string|null
	 */
	public function getWorkspaceName() {
		if (!($currentCollection = $this->getContentCollectionNode()))
			return null;

		return $currentCollection->getContext()->getWorkspaceName();
	}

	/**
	 * Check if we're in Development FLOW_CONTEXT
	 * @todo This is bit nasty hack, find out how to get FLOW_CONTEXT in a more appropriate way.
	 *
	 * @return bool
	 */
	protected function isDevelopmentEnvironment() {
		return isset($_SERVER['FLOW_CONTEXT']) && stristr($_SERVER['FLOW_CONTEXT'], 'Development');
	}

	/**
	 * Inside live workspace, it does NOT render extra DIV.content-collection.
	 *
	 * This is rather dirty/temporary hack as it breaks the rule, that rendered markup
	 * is the same in the Neos back-end and in the front-end.
	 *
	 * @throws \TYPO3\TypoScript\Exception
	 * @return string
	 */
	public function evaluate() {
		if (!($wrapperClass = $this->tsValue('wrapperClass'))) {
			throw new Exception("Missing 'wrapperClass' property for WrapRemover.");
		}

		$content = $this->getValue();
		if ($this->getWorkspaceName() !== 'live') {
			return $content;
		}

		return $this->removeWrapper($content, $wrapperClass);
	}

	/**
	 * Remove extra DIV wrapper, if it contains only the default CSS class
	 * set in wrapperClass TS variable.
	 *
	 * @param string $content
	 * @param string $wrapperClass
	 * @return string
	 */
	public function removeWrapper($content, $wrapperClass) {
		// Do not proceed if the wrapper class is not present in the content
		if (false === strstr($content, $wrapperClass)) {
			return $content;
		}

		$content = preg_replace('#^<div class="'.$wrapperClass.'">(.*)</div>$#ms', '$1', trim($content));

		// In development context add extra HTML comments with info, what is what
		if ($this->isDevelopmentEnvironment() && ($contentCollectionNode = $this->getContentCollectionNode())) {
			// ContentCollection: add context path info around it
			if (strstr($wrapperClass, 'contentcollection')) {
				$contextPath = $contentCollectionNode->getContextPath();
				$content = "<!-- content collection START: $contextPath -->" . $content . "<!-- content collection END: $contextPath -->";
			}
			// Other NodeTypes: add node element name
			elseif (($node = $this->getNode())) {
				$nodeType = $node->getNodeType()->getName();
				$content  = "<!-- $nodeType -->" . $content . "<!-- /$nodeType -->";
			}
		}

		return $content;
	}
}
