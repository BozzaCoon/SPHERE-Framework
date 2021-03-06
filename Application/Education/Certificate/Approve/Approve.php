<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 16:29
 */

namespace SPHERE\Application\Education\Certificate\Approve;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Approve
 *
 * @package SPHERE\Application\Education\Certificate\Approve
 */
class Approve implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse freigeben'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendSelectPrepare')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare' , __NAMESPACE__ . '\Frontend::frontendDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\SetApproved' , __NAMESPACE__ . '\Frontend::frontendApprovePreparePerson')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\ResetApproved' , __NAMESPACE__ . '\Frontend::frontendResetApprovePreparePerson')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Division\SetApproved' , __NAMESPACE__ . '\Frontend::frontendApprovePrepareDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Division\ResetApproved' , __NAMESPACE__ . '\Frontend::frontendResetApprovePrepareDivision')
        );
    }

    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}