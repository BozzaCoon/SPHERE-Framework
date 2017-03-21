<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Account
 * @package SPHERE\Application\Setting\User\Account
 */
class Account implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('/People/User/Account'), new Link\Name('Benutzer anlegen'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account', __NAMESPACE__.'\Frontend::frontendPrepare')
        );
        //FrontendAddress
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Address', __NAMESPACE__.'\Frontend::frontendAddress')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Address/Select', __NAMESPACE__.'\Frontend::frontendAddressSelect')
        );
        //FrontendMail
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Mail', __NAMESPACE__.'\Frontend::frontendMail')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Mail/Select', __NAMESPACE__.'\Frontend::frontendMailSelect')
        );
        //remove from prepare
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyPrepare')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User/Account/Person', __NAMESPACE__.'\Frontend::frontendPreparePersonList')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

}