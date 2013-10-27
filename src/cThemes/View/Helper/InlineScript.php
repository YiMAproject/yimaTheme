<?php
namespace cThemes\View\Helper;

class InlineScript extends HeadScript
{
	/**
	 * Registry key for placeholder
	 * @var string
	 */
	protected $regKey = 'Zend_View_Helper_InlineScript';
	
	/**
	 * Return InlineScript object
	 *
	 * Returns InlineScript helper object; optionally, allows specifying a
	 * script or script file to include.
	 *
	 * @param  string $mode Script or file
	 * @param  string $spec Script/url
	 * @param  string $placement Append, prepend, or set
	 * @param  array $attrs Array of script attributes
	 * @param  string $type Script type and/or array of script attributes
	 * @return \Zend\View\Helper\InlineScript
	 */
	public function __invoke($mode = HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
	{
		return parent::__invoke($mode, $spec, $placement, $attrs, $type);
	}
}
