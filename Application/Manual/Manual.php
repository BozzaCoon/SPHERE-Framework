<?php
namespace SPHERE\Application\Manual;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Manual\General\General;
use SPHERE\Application\Manual\Help\Help;
use SPHERE\Application\Manual\Kreda\Kreda;
use SPHERE\Application\Manual\StyleBook\StyleBook;
use SPHERE\Application\Manual\Support\Support;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Manual
 *
 * @package SPHERE\Application\Manual
 */
class Manual implements IClusterInterface
{

    public static function registerCluster()
    {

        General::registerApplication();
        Kreda::registerApplication();
        StyleBook::registerApplication();
        Help::registerApplication();
        Support::registerApplication();

        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Hilfe & Support'), new Link\Icon(new Question()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Hilfe', 'Tips & Tricks');

        return $Stage;
    }
}
