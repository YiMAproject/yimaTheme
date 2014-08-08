<?php
namespace yimaTheme\Theme;

use yimaTheme\ManagerInterface;

interface LocatorDefaultInterface extends LocatorInterface
{
    /**
     * Inject ThemeManager
     *
     * @param ManagerInterface $manager ThemeManager Object Instance
     *
     * @return mixed
     */
    public function setManager(ManagerInterface $manager);

    /**
     * Get Injected Theme Manager
     *
     * @return ManagerInterface
     */
    public function getManager();
}
