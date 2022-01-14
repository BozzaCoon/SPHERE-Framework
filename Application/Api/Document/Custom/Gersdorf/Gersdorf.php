<?php
namespace SPHERE\Application\Api\Document\Custom\Gersdorf;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Gersdorf extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Emergency/Create', __CLASS__.'::createEmergencyPdf'
        ));
    }

    /**
     * @param null  $PersonId
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createEmergencyPdf($PersonId = null, $Data)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\Emergency', Creator::PAPERORIENTATION_PORTRAIT, $Data);
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}