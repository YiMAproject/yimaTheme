<?php
namespace yimaTheme\Resolvers;

interface ResolverInterface
{
	/**
	 * Attain To Name based on strategy found in class
	 * 
	 * @return mixed|false
	 */
    public function getName();
}
