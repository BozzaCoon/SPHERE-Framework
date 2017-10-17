<?php

namespace SPHERE\Application\Document\Standard\SignOutCertificate;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\Repository\SignOutCertificate\ApiSignOutCertificate;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class SignOutCertificate extends Extension
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Abmeldebescheinigung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillSignOutCertificate'
        ));
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

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Abmeldebescheinigung', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$dataList) {
                    $Data['PersonId'] = $tblPerson->getId();

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('Id' => $tblPerson->getId()))
//                            .new External('Herunterladen',
//                                'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
//                                new Download(), array('Data' => $Data),
//                                'Schulbescheinigung herunterladen')
                    );
                });
            }
        }

        $YearString = '(SJ ';
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            $YearString .= current($tblYearList)->getYear();
        } else {
            $YearString .= new ToolTip(new Danger((new \DateTime())->format('Y')),
                'Kein Schuljahr mit aktuellem Zeitraum');
        }
        $YearString .= ')';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse '.$YearString,
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => 'german-string', 'targets' => 0),
                                        array('width' => '1%', 'targets' => -1),
                                    ),
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendFillSignOutCertificate($Id = null)
    {

        $Stage = new Stage('Schülerüberweisung', 'Erstellen');
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();
        if ($tblPerson) {
            $Global->POST['Data']['FirstLastName'] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $Global->POST['Data']['BirthDate'] = '';
            $Global->POST['Data']['BirthPlace'] = '';
            $Global->POST['Data']['AddressStreet'] = '';
            $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
            if ($tblCommon) {
                if (($tblCommonBirthdate = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['BirthDate'] = $tblCommonBirthdate->getBirthday();
                    $Global->POST['Data']['BirthPlace'] = $tblCommonBirthdate->getBirthplace();
                }
            }
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                if (($tblCity = $tblAddress->getTblCity())) {
                    $Global->POST['Data']['AddressCity'] = $tblCity->getCode().' '.$tblCity->getName();
                }
            }

            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                // Schuldaten der Schule des Schülers
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $tblCompanySchool = $tblStudentTransfer->getServiceTblCompany();
                    if ($tblCompanySchool) {
                        $Global->POST['Data']['School1'] = $tblCompanySchool->getName();
                        $Global->POST['Data']['School2'] = $tblCompanySchool->getExtendedName();
                        $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                        if ($tblAddressSchool) {
                            $Global->POST['Data']['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().' '.$tblAddressSchool->getStreetNumber();
                            $tblCitySchool = $tblAddressSchool->getTblCity();
                            if ($tblCitySchool) {
                                $Global->POST['Data']['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                                $Global->POST['Data']['SchoolCity'] = $tblCitySchool->getName();
                            }
                        }
                    }
                }
                // Datum Aufnahme
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $EntryDate = $tblStudentTransfer->getTransferDate();
                    $Global->POST['Data']['SchoolEntry'] = $EntryDate;
                    if ($EntryDate != '') {
                        $tblYearList = Term::useService()->getYearAllByDate(new \DateTime($EntryDate));
                        if ($tblYearList) {
                            foreach ($tblYearList as $tblYear) {
                                $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                                if ($tblDivision && $tblDivision->getTblLevel()) {
                                    $Global->POST['Data']['SchoolEntryDivision'] = $tblDivision->getTblLevel()->getName();
                                }
                            }
                        }
                    }
                }
            }

            $Global->POST['Data']['PlaceDate'] = $Global->POST['Data']['SchoolCity'].', '.$Global->POST['Data']['Date'];

            // Hauptadresse Schüler
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['MainAddress'] = $tblAddress->getGuiString();
            }

            // Letztes Datum des aktuellen Schuljahres
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                $LastDate = '';
                foreach ($tblYearList as $tblYear) {
                    if (Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear)) {
                        $tblPeriodList = $tblYear->getTblPeriodAll();
                        if ($tblPeriodList) {
                            foreach ($tblPeriodList as $tblPeriod) {
                                if ($LastDate && new \DateTime($LastDate) < new \DateTime($tblPeriod->getToDate())) {
                                    $LastDate = $tblPeriod->getToDate();
                                } elseif (!$LastDate) {
                                    $LastDate = $tblPeriod->getToDate();
                                }
                            }
                        }
                    }
                }
                $Global->POST['Data']['SchoolUntil'] = $LastDate;
            }
        }
        $Global->savePost();

        $form = $this->formSignOut($Id);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

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
                            new Title('Vorlage des Standard-Dokuments "Abmeldebescheinigung"')
//                            .new Success('<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>'
//                                .new Center('Bild')
//                                .'<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>'
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/SignOutCertificate.png')
                                , ''
                            )
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param int $PersonId
     *
     * @return Form
     */
    private function formSignOut($PersonId)
    {

        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Abgebende Schule:')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[School1]', 'Name',
                                                            'Name')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[School2]', 'Zusatz',
                                                            'Zusatz')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[FirstLastName]', 'Name',
                                                            'Vor- und Zuname')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[BirthDate]', 'Geboren am', 'Geboren am')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[BirthPlace]', 'Geboren in', 'Geboren in')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    )
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressCity]', 'PLZ Ort', 'PLZ Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolEntry]', 'Datum', 'Schulbesuch von')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolUntil]', 'Datum', 'Schulbesucht bis')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[PlaceDate]', 'Ort, Datum', 'Ort, Datum')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    )
                                                ),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Aufnehmende Schule:')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchool1]', 'Name',
                                                            'Name')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    ),
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchool2]', 'Zusatz',
                                                            'Zusatz')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    ),
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    ),
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                                                    ),
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                ),

                new FormRow(array(
                    new FormColumn(
                        ApiSignOutCertificate::receiverService(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
                    )
                ))
            ))
        );
    }
}