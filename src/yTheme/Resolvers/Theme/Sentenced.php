<?php
namespace yTheme\Resolvers\Theme;

use yTheme\Resolvers\InterfaceClass;

class Sentenced implements InterfaceClass
{
   protected $name = 'builder';

   public function getName()
   {
       return $this->name;
   }
}