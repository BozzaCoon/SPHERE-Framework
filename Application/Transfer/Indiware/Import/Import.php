<?php

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareError;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class Import extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Setting', 'Consumer', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Indiware', 'Datentransfer');

        $PanelStudentCourseImport[] = new PullClear('Schüler-Kurse SEK II importieren: '.
            new Center(new Standard('', '/Transfer/Indiware/Import/StudentCourse/Prepare', new Upload()
                , array(), 'Hochladen, danach bearbeiten')));
        $tblIndiwareImportStudent = Import::useService()->getIndiwareImportStudentAll(true);
        // load if TblIndiwareImportLectureship exist (by Account)
        if ($tblIndiwareImportStudent) {
            $PanelStudentCourseImport[] = 'Vorhandenen Schüler-Kurse der SEK II bearbeiten: '.
                new Center(new Standard('', '/Transfer/Indiware/Import/StudentCourse/Show', new Edit(), array(),
                        'Bearbeiten')
                    .new Standard('', '/Transfer/Indiware/Import/StudentCourse/Destroy', new Remove(), array(),
                        'Löschen'));
        }

        $PanelLectureshipImport[] = new PullClear('Lehraufträge importieren: '.
            new Center(new Standard('', '/Transfer/Indiware/Import/Lectureship/Prepare', new Upload()
                , array(), 'Hochladen, danach bearbeiten')));
        $tblIndiwareImportLectureship = Import::useService()->getIndiwareImportLectureshipAll(true);
        // load if TblIndiwareImportLectureship exist (by Account)
        if ($tblIndiwareImportLectureship) {
            $PanelLectureshipImport[] = 'Vorhandenen Import der Lehraufträge bearbeiten: '.
                new Center(new Standard('', '/Transfer/Indiware/Import/Lectureship/Show', new Edit(), array(),
                        'Bearbeiten')
                    .new Standard('', '/Transfer/Indiware/Import/Lectureship/Destroy', new Remove(), array(),
                        'Löschen'));
        }
        $tblIndiwareError = Import::useService()->getIndiwareErrorByType(TblIndiwareError::TYPE_LECTURE_SHIP);
        // load if TblIndiwareImportLectureship exist (by Account)
        if ($tblIndiwareError) {
            $PanelLectureshipImport[] = 'Importfehler des letzten Uploads'.
                new Center(new External('', '/Api/Transfer/Indiware/ErrorExcel/LectureShip/Download', new Download(),
                    array(
                        'Type' => TblIndiwareError::TYPE_LECTURE_SHIP,
                        'StringCompareDescription' => 'Klasse_Fach_Lehrer(_Fachgruppe)'
                    )
                    , 'Herunterladen'));
        }

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Indiware-Import für Lehraufträge', $PanelLectureshipImport
                                , Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Indiware-Import für Schüler-Kurse SEK II', $PanelStudentCourseImport
                                , Panel::PANEL_TYPE_INFO)
                            , 4),
                    ))
                )
            )
        );

        return $Stage;
    }
}