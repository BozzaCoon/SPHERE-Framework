<?php
namespace SPHERE\Application\Transfer\Import\Atlantis;

use DateTime;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Atlantis
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $File
     * @param null $Data
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStudentImport($File = null, $Data = null)
    {

        $Stage = new Stage('Import', 'Standard für Schüler');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Import', new ChevronLeft()));

        $Now = new DateTime();
        $Year = (int)$Now->format('Y');
        $YearShort = (int)$Now->format('y');
        $YearList = array(
            ($Year - 2).'/'.($YearShort - 1) => ($Year - 2).'/'.($YearShort - 1),
            ($Year - 1).'/'.$YearShort => ($Year - 1).'/'.$YearShort,
            $Year.'/'.($YearShort + 1) => $Year.'/'.($YearShort + 1),
            ($Year + 1).'/'.($YearShort + 2) => ($Year + 1).'/'.($YearShort + 2),
            );

        if((new DateTime())->format('m') < 8) {
            $_POST['Data']['Year'] = ($Year - 1).'/'.$YearShort;
        } else {
            $_POST['Data']['Year'] = $Year.'/'.($YearShort + 1);
        }

        $NationArray = ImportAtlantis::useService()->Nation;
        $NationList = 'Id&nbsp;&nbsp;&nbsp; => Land';
        foreach($NationArray as $Id => $Nation) {
            $Space = '';
            if(strlen($Id) == 1){
                $Space = '&nbsp;&nbsp;&nbsp;&nbsp;';
            } elseif(strlen($Id) == 2){
                $Space = '&nbsp;&nbsp;';
            }
            $NationList .= '<br/> '.$Id.$Space.' => '.$Nation;
        }
        $SubjectArray = ImportAtlantis::useService()->Subject;
        $SubjectList = 'Id&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; => Fach';
        foreach($SubjectArray as $Id => $Subject) {
            $SubjectList .= '<br/> '.$Id.' => '.$Subject;
        }
        $ReligionArray = ImportAtlantis::useService()->Religion;
        $ReligionList = 'Id&nbsp;&nbsp; => Religion';
        foreach($ReligionArray as $Id => $Religion) {
            $ReligionList .= '<br/> '.$Id.' => '.$Religion;
        }

//        $NationCount = 18;
//        $NationIdFieldList = array();
//        $NationNameFieldList = array();
//        for($i = 0; $i < $NationCount; $i++) {
////            $NationIdFieldList[] = new FormColumn(new TextField('FieldList[NationId'.$i.']', '', 'Land Id'));
////            $NationNameFieldList[] = new FormColumn(new TextField('FieldList[NationName'.$i.']', '', 'Land Name'));
//        }
//        $SubjectCount = 10;
//        $SubjectIdFieldList = array();
//        $SubjectNameFieldList = array();
//        for($i = 0; $i < $SubjectCount; $i++) {
////            $SubjectIdFieldList[] = new FormColumn(new TextField('FieldList[SubjectId'.$i.']', '', 'Fach Id'));
////            $SubjectNameFieldList[] = new FormColumn(new TextField('FieldList[SubjectName'.$i.']', '', 'Fach Name'));
//        }
//        $ReligionCount = 10;
//        $ReligionIdFieldList = array();
//        $ReligionNameFieldList = array();
//        for($i = 0; $i < $ReligionCount; $i++) {
////            $ReligionIdFieldList[] = new FormColumn(new TextField('FieldList[ReligionId'.$i.']', '', 'Religion Id'));
////            $ReligionNameFieldList[] = new FormColumn(new TextField('FieldList[ReligionName'.$i.']', '', 'Religion Name'));
//        }


        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new Well(
                    ImportAtlantis::useService()->createStudentsFromFile(
                        new Form(new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                        array('showPreview' => false))
                                    , 8),
                                new FormColumn(
                                    new SelectBox('Data[Year]', 'Für welches Schuljahr gilt der Import', $YearList, null, false)
                                    , 4)
                            )),
                            new FormRow(array(
                                new FormColumn(new Info('Vordefinierte Id\'s kontrollieren ob diese Stimmen ggf. im Quellcode(service) korrekt setzen/erweitern', null, false, 5, 5), 12),
                                new FormColumn(new Listing(array($NationList)), 4),
                                new FormColumn(new Listing(array($SubjectList)), 4),
                                new FormColumn(new Listing(array($ReligionList)), 4),
//                                new FormColumn(new Form(new FormGroup(new FormRow($NationIdFieldList))), 1),
//                                new FormColumn(new Form(new FormGroup(new FormRow($NationNameFieldList))), 3),
//                                new FormColumn(new Form(new FormGroup(new FormRow($SubjectIdFieldList))), 1),
//                                new FormColumn(new Form(new FormGroup(new FormRow($SubjectNameFieldList))), 3),
//                                new FormColumn(new Form(new FormGroup(new FormRow($ReligionIdFieldList))), 1),
//                                new FormColumn(new Form(new FormGroup(new FormRow($ReligionNameFieldList))), 3),
                            )),

                        )), new Primary('Hochladen'))
                        , $File, $Data
                    )
                    .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                )
            )))))
        );

        return $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStuffImport($File = null)
    {

        $Stage = new Stage('Import', 'Standard für Mitarbeiter/Lehrer');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new Well(
                    ImportAtlantis::useService()->createStaffFromFile(
                        new Form(new FormGroup(new FormRow(new FormColumn(
                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                array('showPreview' => false))
                        ))), new Primary('Hochladen'))
                        , $File
                    )
                    .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                )
            )))))
        );

        return $Stage;
    }
}