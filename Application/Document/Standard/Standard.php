<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 09:14
 */

namespace SPHERE\Application\Document\Standard;

use SPHERE\Application\Document\Standard\AccidentReport\AccidentReport;
use SPHERE\Application\Document\Standard\EnrollmentDocument\EnrollmentDocument;
use SPHERE\Application\Document\Standard\SignOutCertificate\SignOutCertificate;
use SPHERE\Application\Document\Standard\StudentCard\StudentCard;
use SPHERE\Application\Document\Standard\StudentTransfer\StudentTransfer;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Standard
 *
 * @package SPHERE\Application\Document\Standard
 */
class Standard implements IApplicationInterface
{

    public static function registerApplication()
    {

        EnrollmentDocument::registerModule();
        StudentCard::registerModule();
        AccidentReport::registerModule();
        StudentTransfer::registerModule();
        SignOutCertificate::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Standard'))
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

        $Stage = new Stage('Standard', 'Dashboard');

        return $Stage;
    }
}