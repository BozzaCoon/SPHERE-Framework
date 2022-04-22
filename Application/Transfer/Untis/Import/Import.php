<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
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
        return new Service(new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
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

        $Stage = new Stage('Untis', 'Datentransfer');

        $PanelLectureshipImport[] = new PullClear('Lehraufträge importieren: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Lectureship/Prepare', new Upload()
                , array(), 'Hochladen, danach bearbeiten')));
        $tblUntisImportLectureship = Import::useService()->getUntisImportLectureshipAll(true);
        // load if TblUntisImportLectureship exist (by Account)
        if ($tblUntisImportLectureship) {
            $PanelLectureshipImport[] = 'Vorhandenen Import der Lehraufträge bearbeiten: '.
                new Center(new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array(), 'Bearbeiten')
                    .new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array(), 'Löschen'));
        }

        $PanelStudentCourse[] = new PullClear('Schüler-Kurse SEK II importieren: '.
            new Center(new Standard('', '/Transfer/Untis/Import/StudentCourse/Prepare', new Upload()
                , array(), 'Hochladen, danach bearbeiten')));
//        $tblUntisImportStudentCourse = Import::useService()->getUntisImportStudentCourseAll(true);
        $tblUntisImportStudentCourse = Import::useService()->getUntisImportStudentAll(true);
        // load if TblUntisImportLectureship exist (by Account)
        if ($tblUntisImportStudentCourse) {
            $PanelStudentCourse[] = 'Vorhandenen Import der Schüler-Kurse SEK II bearbeiten: '.
                new Center(new Standard('', '/Transfer/Untis/Import/StudentCourse/Show', new Edit(), array(), 'Bearbeiten')
                    .new Standard('', '/Transfer/Untis/Import/StudentCourse/Destroy', new Remove(), array(), 'Löschen'));
        }
        $PanelTimetable[] = new PullClear('Stundenplan aus Untis: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Timetable', new Upload())));
        $PanelTimetableReplacement[] = new PullClear('Vertretungsplan aus Untis: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Replacement', new Upload())));

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Warning(
                            new Container('Bitte beachten Sie die Reihenfolge für den Import:').
                            new Container('1. Untis-Import für Schüler-Kurse SEK II').
                            new Container('2. Untis-Import für Lehraufträge')
                        )
                    )
                ),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Untis-Import für Schüler-Kurse SEK II:', $PanelStudentCourse
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Panel('Untis-Import für Lehraufträge:', $PanelLectureshipImport
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Ruler()
                    ),
                    new LayoutColumn(
                        new Panel('Import Stundenplan:', $PanelTimetable
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Panel('Import Vertretungsplan:', $PanelTimetableReplacement
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                ))
            )))
        );

        return $Stage;
    }
}