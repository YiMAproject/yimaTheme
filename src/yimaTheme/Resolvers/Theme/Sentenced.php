<?php
namespace yimaTheme\Resolvers\Theme;

use yimaTheme\Resolvers\ResolverInterface;

class Sentenced implements
    ResolverInterface
{
   protected $name = 'builder';

   public function getName()
   {
       return $this->name;
   }
}