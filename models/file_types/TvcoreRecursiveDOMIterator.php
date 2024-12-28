<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class TvcoreRecursiveDOMIterator implements RecursiveIterator
{
    protected int $_position;

    /**
     * The DOMNodeList with all children to iterate over
     * @var DOMNodeList
     */
    protected DOMNodeList $_nodeList;

    /**
     * @param DOMNode $domNode
     * @return void
     */
    public function __construct(DOMNode $domNode)
    {
        $this->_position = 0;
        $this->_nodeList = $domNode->childNodes;
    }

    /**
     * Returns the current DOMNode
     * @return DOMNameSpaceNode|DOMElement|DOMNode|null
     */
    public function current(): DOMNameSpaceNode|DOMElement|null|DOMNode
    {
        return $this->_nodeList->item($this->_position);
    }

    /**
     * Returns an iterator for the current iterator entry
     * @return RecursiveIterator|null
     */
    public function getChildren(): ?RecursiveIterator
    {
        return new self($this->current());
    }

    /**
     * Returns if an iterator can be created for the current entry.
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->current()->hasChildNodes();
    }

    /**
     * Returns the current position
     * @return int
     */
    public function key(): int
    {
        return $this->_position;
    }

    /**
     * Moves the current position to the next element.
     * @return void
     */
    public function next(): void
    {
        ++$this->_position;
    }

    /**
     * Rewind the Iterator to the first element
     * @return void
     */
    public function rewind(): void
    {
        $this->_position = 0;
    }

    /**
     * Checks if current position is valid
     * @return bool
     */
    public function valid(): bool
    {
        return $this->_position < $this->_nodeList->length;
    }
}
