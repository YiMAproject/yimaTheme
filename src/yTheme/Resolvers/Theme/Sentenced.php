<?php
namespace yTheme\Resolvers\Theme;

use yTheme\Resolvers\ResolverInterface;

class Sentenced implements
    ResolverInterface
{
   protected $name = 'builder';

   public function getName()
   {
       return $this->name;
   }
}