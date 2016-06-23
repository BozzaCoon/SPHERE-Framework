<?php
namespace SPHERE\Application\Api\Platform\Database;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\More;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\CookieHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

class Database extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Upgrade', __CLASS__.'::executeUpgrade'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    public function executeUpgrade($Consumer)
    {

        $Result = array();
        if ($Consumer) {
            $tblConsumer = Consumer::useService()->getConsumerByAcronym($Consumer);
            if ($tblConsumer) {

                Account::useService()->changeConsumer($tblConsumer);

                $Break = 10;
                $Acronym = '';
                while (( ( $Break-- ) > 0 ) && ( strtoupper($Consumer) != strtoupper($Acronym) )) {

                    $this->getCache(new CookieHandler())->clearCache();
                    $this->getCache(new MemcachedHandler())->clearCache();
                    $this->getCache(new APCuHandler())->clearCache();
                    $this->getCache(new MemoryHandler())->clearCache();
                    $this->getCache(new OpCacheHandler())->clearCache();
                    $this->getCache(new TwigHandler())->clearCache();
                    $this->getCache(new SmartyHandler())->clearCache();

                    $Acronym = Consumer::useService()->getConsumerBySession()->getAcronym();
                }

                Main::registerGuiPlatform();

                $Protocol = (new \SPHERE\Application\Platform\System\Database\Database())->frontendSetup(false, true);
                $Result = $Acronym.' '.(new Accordion(false))->addItem(new More().' Protocol für '.$Consumer.' (Execution on: '.$Acronym.')',
                        $Protocol->getContent())->getContent();
            } else {
                return json_encode($Consumer.' '.(new Danger('Mandant '.$Consumer.' not valid!')));
            }
        }
        return json_encode($Result);
    }
}
