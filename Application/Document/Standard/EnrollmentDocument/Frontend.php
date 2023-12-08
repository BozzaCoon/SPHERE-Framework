<?php
namespace SPHERE\Application\Document\Standard\EnrollmentDocument;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Document\Standard\EnrollmentDocument
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param Stage $Stage
     */
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/EnrollmentDocument', new Person(), array(), 'Schulbescheinigung eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/EnrollmentDocument/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Kurs')), '/Document/Standard/EnrollmentDocument/Division', new PersonGroup(),
                array(), 'Schulbescheinigungen eines Kurses'));
        } else {
            $Stage->addButton(new Standard('Kurs', '/Document/Standard/EnrollmentDocument/Division', new PersonGroup(),
                array(), 'Schulbescheinigungen eines Kurses'));
        }
    }

    /**
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public static function frontendSelectDivision(bool $IsAllYears = false, ?string $YearId = null): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Kurs auswählen');
        self::setButtonList($Stage);

        list($yearButtonList, $filterYearList)
            = Term::useFrontend()->getYearButtonsAndYearFilters('/Document/Standard/EnrollmentDocument/Division', $IsAllYears, $YearId);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        empty($yearButtonList) ? '' : $yearButtonList
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            EnrollmentDocument::useFrontend()->loadDivisionTable($filterYearList, 'EnrollmentDocument')
                        )
                    )), new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @param array $filterYearList
     *
     * @return TableData
     */
    public function loadDivisionTable(array $filterYearList, string $documentType = 'EnrollmentDocument'): TableData
    {
        switch ($documentType) {
            case 'EnrollmentDocument': $documentName = 'Schulbescheinigungen'; break;
            case 'SignOutCertificate': $documentName = 'Abmeldebescheinigung'; break;
            default: $documentName = 'Dokument';
        }

        $dataList = array();
        $tblDivisionCourseList = array();
        if ($filterYearList) {
            foreach ($filterYearList as $tblYear) {
                if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                    $tblDivisionCourseList = $tblDivisionCourseListDivision;
                }
                if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                    TblDivisionCourseType::TYPE_CORE_GROUP))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                }
            }
        } else {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $count = $tblDivisionCourse->getCountStudents();
            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Count' => $count,
                'Option' => $count > 0
                    ? (new External(
                        '',
                        '/Api/Document/Standard/' . $documentType .'/CreateMulti',
                        new Download(),
                        array(
                            'DivisionCourseId' => $tblDivisionCourse->getId()
                        ),
                        $documentName . ' herunterladen'
                    ))->__toString()
                    : ''
            );
        }

        return new TableData($dataList, null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseType' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
                'responsive' => false
            ));
    }

    /**
     * @return Stage
     */
    public static function frontendEnrollmentDocument(): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');
        self::setButtonList($Stage);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            self::getStudentSelectDataTable('/Document/Standard/EnrollmentDocument/Fill')
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param string $route
     *
     * @return TableData
     */
    public static function getStudentSelectDataTable(string $route): TableData
    {
        $dataList = array();
        $showDivision = false;
        $showCoreGroup = false;
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $displayDivision = '';
                $displayCoreGroup = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if (($tblDivision = $tblStudentEducation->getTblDivision())
                        && ($displayDivision = $tblDivision->getName())
                    ) {
                        $showDivision = true;
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        && ($displayCoreGroup = $tblCoreGroup->getName())
                    ) {
                        $showCoreGroup = true;
                    }
                }
                $tblAddress = $tblPerson->fetchMainAddress();
                $dataList[] = array(
                    'Name'     => $tblPerson->getLastFirstName(),
                    'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                    'Division' => $displayDivision,
                    'CoreGroup' => $displayCoreGroup,
                    'Option'   => new Standard(
                        'Erstellen',
                        $route,
                        null,
                        array('PersonId' => $tblPerson->getId())
                    )
                );
            }
        }

        $columnList['Name'] = 'Name';
        $columnList['Address'] = 'Adresse';
        if ($showDivision) {
            $columnList['Division'] = 'Klasse';
        }
        if ($showCoreGroup) {
            $columnList['CoreGroup'] = 'Stammgruppe';
        }
        $columnList['Option'] = '';

        return new TableData(
            $dataList,
            null,
            $columnList,
            array(
                "columnDefs" => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param string|null $PersonId
     *
     * @return Stage
     */
    public function frontendFillEnrollmentDocument(?string $PersonId = null): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/EnrollmentDocument', new ChevronLeft()));
        $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Data = array();
        if ($tblPerson) {
            $Data = EnrollmentDocument::useService()->getEnrollmentDocumentData($tblPerson);
            $Global->POST['Data'] = $Data;
        }
        $Global->savePost();

        if(ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
            // Berlin
            $form = $this->formStudentDocumentEKBO($Data['Gender'] ?? false);
        } else {
            // Sachsen
            $form = $this->formStudentDocument($Data['Gender'] ?? false);
        }

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External(
            'Blanko Schulbescheinigung herunterladen',
            'SPHERE\Application\Api\Document\Standard\EnrollmentDocument\Create',
            new Download(),
            array('Data' => array('empty')),
            'Schulbescheinigung herunterladen'
        ));
        // Standard (Sachsen)
        $Thumbnail = new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Document/Schulbescheinigung.PNG'), '');
        if(ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
            // EKBO
            $Thumbnail = new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Document/Schulbescheinigung_EKBO.PNG'), '');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                        , 7)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                        , 7),
                        new LayoutColumn(
                            new Title('Vorlage des Standard-Dokuments "Schulbescheinigung"')
                            . $Thumbnail
                        , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param $Gender
     *
     * @return Form
     */
    private function formStudentDocumentEKBO($Gender): Form
    {

        // Berlin
        return new Form(
            new FormGroup(array(
                new FormRow(array(
//                        new FormColumn(array(
//                            new HiddenField('Data[PersonId]'),
//                            new HiddenField('Data[SchoolId]'),
//                        )),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School]', 'Schule', 'Schule')
                                                    , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolExtended]', 'Zusatz', 'Zusatz')
                                                    , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressDistrict]', 'Ortsteil', 'Ortsteil')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.', 'Straße Hausnummer')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort', 'PLZ Ort')
                                                    , 4)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanySchoolLeader]', 'Schulleiter(in)', 'Schulleiter(in)')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanySecretary]', 'Sekretariat', 'Sekretariat')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanyMail]', 'E-Mail', 'E-Mail')
                                                    , 4),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanyPhone]', 'Telefon', 'Telefon')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanyFax]', 'Fax', 'Fax')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanyWeb]', 'Internet', 'Internet')
                                                    , 4),
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastName]', 'Vorname, Name',
                                                            'Vorname, Name '.
                                                            ($Gender == 'Männlich'
                                                                ? 'des Schülers'
                                                                : ($Gender == 'Weiblich'
                                                                    ? 'der Schülerin'
                                                                    : 'des Schülers/der Schülerin')
                                                            ))
                                                    , 8),
                                                    new LayoutColumn(
                                                        new TextField('Data[Gender]', 'Geschlecht', 'Geschlecht')
                                                    , 4)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geboren am', 'Geburtstag')
                                                    , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthplace]', 'Geboren in', 'Geburtsort')
                                                    , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressDistrict]', 'Ortsteil', 'Ortsteil')
                                                    , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer', 'Straße, Hausnummer')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl', 'Postleitzahl')
                                                    , 2),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort', 'Ort')
                                                    , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Division]', 'Besucht zur Zeit die Klasse', 'Besucht zur Zeit die Klasse')
                                                    , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[LeaveDate]', 'Voraussichtlich bis', 'Voraussichtlich bis')
                                                    , 6)
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Dokument')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Place]', 'PLZ Ort', 'PLZ Ort')
                                                    , 6),
                                                    new LayoutColumn(
                                                        new DatePicker('Data[Date]', 'Datum', 'Datum')
                                                    , 6)
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Standard\EnrollmentDocument\Create'
        );
    }

    /**
     * @param $Gender
     *
     * @return Form
     */
    private function formStudentDocument($Gender): Form
    {

        // Sachsen
        return new Form(
            new FormGroup(array(
                new FormRow(array(
//                        new FormColumn(array(
//                            new HiddenField('Data[PersonId]'),
//                            new HiddenField('Data[SchoolId]'),
//                        )),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School]', 'Schule', 'Schule')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolExtended]', 'Zusatz', 'Zusatz')
                                                        , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressDistrict]', 'Ortsteil', 'Ortsteil')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.', 'Straße Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort', 'PLZ Ort')
                                                        , 4)
                                                ))
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastName]', 'Vorname, Name',
                                                            'Vorname, Name '.
                                                            ($Gender == 'Männlich'
                                                                ? 'des Schülers'
                                                                : ($Gender == 'Weiblich'
                                                                    ? 'der Schülerin'
                                                                    : 'des Schülers/der Schülerin')
                                                            ))
                                                        , 8),
                                                    new LayoutColumn(
                                                        new TextField('Data[Gender]', 'Geschlecht', 'Geschlecht')
                                                        , 4)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geboren am', 'Geburtstag')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthplace]', 'Geboren in', 'Geburtsort')
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressDistrict]', 'Ortsteil', 'Ortsteil')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer', 'Straße, Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl', 'Postleitzahl')
                                                        , 2),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort', 'Ort')
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Division]', 'Besucht zur Zeit die Klasse', 'Besucht zur Zeit die Klasse')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[LeaveDate]', 'Voraussichtlich bis', 'Voraussichtlich bis')
                                                        , 6)
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Dokument')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Place]', 'Ort', 'Ort')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new DatePicker('Data[Date]', 'Datum', 'Datum')
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Standard\EnrollmentDocument\Create'
        );
    }
}