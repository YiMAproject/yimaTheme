<?php
namespace yTheme\View\Helper;

use Zend\View\Helper\HeadLink as ZendHeadLink;

class HeadLink extends ZendHeadLink
{
    public function toString($indent = null)
    {
    	return '<!-- HeadLink output not allowed by yTheme. -->';
    }
    
    /**
     * Render link elements as string
     *
     * @param  string|int $indent
     * @return string
     */
    public function getHtmlTags($indent = null)
    {
    	$indent = (null !== $indent)
    	? $this->getWhitespace($indent)
    	: $this->getIndent();
    	
    	$items = array();
    	$this->getContainer()->ksort();
    	foreach ($this as $item) {
    		$items[] = $this->itemToString($item);
    	}
    	
    	return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }
}
