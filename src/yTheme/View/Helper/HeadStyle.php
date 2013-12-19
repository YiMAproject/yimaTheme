<?php
namespace yTheme\View\Helper;

use Zend\View\Helper\HeadStyle as ZendHeadStyle;

class HeadStyle extends ZendHeadStyle
{
    public function toString($indent = null)
    {
    	return '<!-- HeadStyle output not allowed by yTheme. -->';
    }
    
    /**
     * Create string representation of placeholder
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
            if (!$this->isValid($item)) {
                continue;
            }
            $items[] = $this->itemToString($item, $indent);
        }

        $return = $indent . implode($this->getSeparator() . $indent, $items);
        $return = preg_replace("/(\r\n?|\n)/", '$1' . $indent, $return);
        return $return;
    }
}
