<?php
namespace SPHERE\Application\Transfer\Import\Atlantis;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Main;

/**
 * Class ImportAtlantis
 *
 * @package SPHERE\Application\Transfer\Import\Atlantis
 */
class ImportAtlantis implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Interested', __NAMESPACE__.'\Frontend::frontendInterestedImport'
//        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Stuff', __NAMESPACE__.'\Frontend::frontendStuffImport'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Company', __NAMESPACE__.'\Frontend::frontendCompanyImport'
//        ));
    }

    /**
     * @return LayoutColumn[]
     */
    public static function getAtlantisLink()
    {
        $ColumnList = array();
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Atlantis.png'), 'Schülerdaten', '',
                new Primary('', __NAMESPACE__.'/Student', new Upload())
            ), 2);
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Atlantis.png'), 'Mitarbeiter/Lehrer', '',
                new Primary('', __NAMESPACE__.'/Stuff', new Upload())
            ), 2);

        return $ColumnList;
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}