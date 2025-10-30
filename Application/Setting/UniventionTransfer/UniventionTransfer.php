<?php

namespace SPHERE\Application\Setting\UniventionTransfer;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

class UniventionTransfer implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

//        Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
//            new Link\Name('DLLP'), new Link\Icon(new Publicly())
//        ));
//
//
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, __NAMESPACE__.'/Frontend::frontendUnivention'
//        ));

    }

    public static function useService()
    {

        return new Service(new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

}