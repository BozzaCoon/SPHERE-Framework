<?php
namespace SPHERE\Application\Contact\Web;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Web
 *
 * @package SPHERE\Application\Contact\Mail
 */
class Web implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {
        // Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Contact', 'Web', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
