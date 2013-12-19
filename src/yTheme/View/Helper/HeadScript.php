<?php
namespace yTheme\View\Helper;

use Zend\View\Helper\HeadScript as ZendHeadScript;

class HeadScript extends ZendHeadScript
{
    public function toString($indent = null)
    {
    	return '<!-- HeadScript output not allowed by yTheme. -->';
    }
    
    /**
     * Retrieve string representation
     *
     * @param  string|int $indent Amount of whitespaces or string to use for indention
     * @return string
     */
    public function getHtmlTags($indent = null)
    {
    	$indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->plugin('doctype')->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }

        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>' : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        $return = implode($this->getSeparator(), $items);
        return $return;
    }
}
