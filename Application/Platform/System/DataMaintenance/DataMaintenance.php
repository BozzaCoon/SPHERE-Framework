<?php

namespace SPHERE\Application\Platform\System\DataMaintenance;

use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradeMaintenance;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class DataMaintenance
 * @package SPHERE\Application\Platform\System\DataMaintenance
 */
class DataMaintenance
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datenpflege'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __CLASS__.'::frontendDataMaintenance'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/OverView',
                __CLASS__.'::frontendUserAccount'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __CLASS__.'::frontendDestroyAccount'
            )
        );
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Integration',
//                __CLASS__.'::frontendTransferOltIntegration'
//            )
//        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Restore/Person',
                __CLASS__.'::frontendPersonRestore'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Restore\Person\Selected',
                __CLASS__ . '::frontendPersonRestoreSelected'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Grade',
                __CLASS__ . '::frontendGrade'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeUnreachable',
                __CLASS__ . '::frontendGradeUnreachable'
            )
        );
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

    public function frontendDataMaintenance()
    {

        $Stage = new Stage('Datenpflege');
        // Schüler Account Zählung
        $tblUserAccountList = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $StudentAccountCount = ($tblUserAccountList ? count($tblUserAccountList) : 0);
        // Sorgeberechtigte Account Zählung
        $tblUserAccountList = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $CustodyAccountCount = ($tblUserAccountList ? count($tblUserAccountList) : 0);

        // Import Integration
//        $IsImport = false;
//        if(Student::useService()->countSupportAll() !== '0'){
//            $IsImport = true;
//        }
//        if(!$IsImport && Student::useService()->countSpecialAll() !== '0'){
//            $IsImport = true;
//        }
//
//        $ImportCount = 0;
//        if(!$IsImport && ($tblStudentAll = Student::useService()->getStudentAll())) {
//            foreach ($tblStudentAll as $tblStudent) {
//                $tblPerson = $tblStudent->getServiceTblPerson();
//                if (($tblStudentIntegration = $tblStudent->getTblStudentIntegration())) {
//                    $Request = $tblStudentIntegration->getCoachingRequestDate();
//                    $Counsel = $tblStudentIntegration->getCoachingCounselDate();
//                    $Decision = $tblStudentIntegration->getCoachingDecisionDate();
//                    if ($tblPerson && ($Request || $Counsel || $Decision)) {
//                        $ImportCount++;
//                    }
//                }
//            }
//        }
//        if($IsImport){
//            $IntegrationColumn = new LayoutColumn('');
//        } else {
//            $IntegrationColumn = new LayoutColumn(array(
//                new TitleLayout('Integration', 'Übernehmen'),
//                new Standard('Import aus alter Datenbank '.new Label($ImportCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/Integration', new CogWheels())
//            ));
//        }

        // SoftRemoved Person
        $CountSoftRemovePerson = 0;
        if (($tblPersonList = Person::useService()->getPersonAllBySoftRemove())) {
            $CountSoftRemovePerson = count($tblPersonList);
        }


        $Stage->setContent( new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new TitleLayout('Gelöschte Personen'),
                        ($CountSoftRemovePerson >= 1
                            ? new Standard('Personenübersicht ('.$CountSoftRemovePerson.')', __NAMESPACE__.'/Restore/Person')
                            : new Success('Keine Personen gelöscht')
                        )
                    )),
                    new LayoutColumn(array(
                        new TitleLayout('Benutzer-Accounts löschen'),
                        new Standard('Alle Schüler '.new Label($StudentAccountCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/OverView', new EyeOpen(),
                            array('AccountType' => 'STUDENT')),
                        new Standard('Alle Sorgeberechtigte '.new Label($CustodyAccountCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/OverView', new EyeOpen(),
                            array('AccountType' => 'CUSTODY'))
                    )),
                    new LayoutColumn(array(
                        new TitleLayout('Zensuren/Noten'),
                        (new Standard('Verschieben', __NAMESPACE__.'/Grade'))
                            .  (new Standard('Unerreichbare Zensuren', __NAMESPACE__.'/GradeUnreachable'))
                    ))
//                    $IntegrationColumn,
                ))
            )
        ));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendPersonRestore()
    {
        $Stage = new Stage('Personen Wiederherstellen', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $dataList = array();
        if (($tblPersonList = Person::useService()->getPersonAllBySoftRemove())) {
            foreach ($tblPersonList as $tblPerson) {
                if (($date = $tblPerson->getEntityRemove())) {
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson, true);
                    $dataList[] = array(
                        'EntityRemove' => $date->format('d.m.Y'),
                        'Time' => $date->format('H:i:s'),
                        'Name' => $tblPerson->getLastFirstName(),
                        'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Option' => new Standard(
                            '',
                            '\Platform\System\DataMaintenance\Restore\Person\Selected',
                            new EyeOpen(),
                            array(
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Anzeigen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            empty($dataList)
                ? new Warning('Es sind keine soft gelöschten Person vorhanden.', new Exclamation())
                : new TableData(
                $dataList,
                null,
                array(
                    'EntityRemove' => 'Gelöscht am',
                    'Time' => 'Uhrzeit',
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => ''
                ),
                array(
                    'order' => array(
                        array('0', 'desc'),
                        array('1', 'desc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'de_date', 'targets' => 0),
                        array('type' => 'de_time', 'targets' => 1),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                        array('width' => '1%', 'targets' => -1),
                    ),
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     * @param bool $IsRestore
     *
     * @return Stage|string
     */
    public function frontendPersonRestoreSelected($PersonId = null, $IsRestore = false)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId, true))) {
            $Stage = new Stage('Person Wiederherstellen', 'Anzeigen');
            $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/Restore/Person', new ChevronLeft()));

            if (!$IsRestore) {
                $Stage->addButton(
                    new Standard('Alle Daten wiederherstellen', __NAMESPACE__.'/Restore/Person/Selected', new Upload(),
                        array(
                            'PersonId' => $PersonId,
                            'IsRestore' => true
                        )
                    )
                );
            }

            if ($IsRestore) {
                $columns =  array(
                    'Number' => '#',
                    'Type' => 'Typ',
                    'Value' => 'Wert'
                );
            } else {
                $columns =  array(
                    'Number' => '#',
                    'Type' => 'Typ',
                    'Value' => 'Wert',
                    'EntityRemove' => 'Gelöscht am'
                );
            }

            $Stage->setContent(
                ($IsRestore ? new Success('Die Daten wurden wieder hergestellt.', new SuccessIcon()) : '')
                . new TableData(Person::useService()->getRestoreDetailList($tblPerson, $IsRestore), null, $columns,
                    array(
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                    )
                )
            );

            return $Stage;
        } else {
            return new Stage('Person Wiederherstellen', 'Anzeigen')
                . new Danger('Die Person wurde nicht gefunden', new Exclamation())
                . new Redirect(__NAMESPACE__.'/Restore/Person', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $AccountType
     *
     * @return Stage
     */
    public function frontendUserAccount($AccountType = null)
    {

        $Stage = new Stage('Datenpflege', '');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $TableContent = array();
        if ($AccountType) {
            $tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType);
            if ($tblUserAccountList) {
                array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableContent) {
                    $Item['Account'] = '';
                    $Item['User'] = '';
                    $Item['Type'] = '';

                    $tblAccount = $tblUserAccount->getServiceTblAccount();
                    if ($tblAccount) {
                        $Item['Account'] = $tblAccount->getUsername();
                        $tblUserList = AccountAuthorization::useService()->getUserAllByAccount($tblAccount);
                        /** @var TblUser $tblUser */
                        if ($tblUserList && ($tblUser = current($tblUserList))) {
                            $tblPerson = $tblUser->getServiceTblPerson();
                            if ($tblPerson) {
                                $Item['User'] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                    $Type = $tblUserAccount->getType();
                    if ($Type === 'STUDENT') {
                        $Item['Type'] = 'Schüler';
                    } elseif ($Type === 'CUSODY') {
                        $Item['Type'] = 'Sorgeberechtigte';
                    }
                    array_push($TableContent, $Item);
                });
            } else {
                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Warning('Es sind keine Accounts für den Typ: "'.$AccountType.'" vorhanden')
                                )
                            )
                        )
                    )
                );
            }
        }
        if (!empty($TableContent)) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Account' => 'Benutzer-Account',
                                        'User'    => 'Person',
                                        'Type'    => 'Account-Typ'
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Löschen', __NAMESPACE__.'/Destroy', new Remove(),
                                    array('AccountType' => $AccountType))
                            )
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param string $AccountType
     * @param bool   $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyAccount($AccountType, $Confirm = false)
    {

        $Stage = new Stage('Benutzeraccounts', 'Löschen');

        if (($tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType))) {
            $Stage->addButton(new Standard(
                'Zurück', __NAMESPACE__, new ChevronLeft()
            ));

            if (!$Confirm) {
                $Type = 'Unbekannt';
                if ($AccountType == 'STUDENT') {
                    $Type = 'Schüler';
                } elseif ($AccountType == 'CUSTODY') {
                    $Type = 'Sorgeberechtigte';
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question().' Löschabfrage',
                            'Sollen die Accounts mit dem Typ "'.new Bold($Type).'" wirklich gelöscht werden? (Anzahl: '.count($tblUserAccountList).')',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', __NAMESPACE__.'/Destroy', new Ok(),
                                array(
                                    'AccountType' => $AccountType,
                                    'Confirm'     => true
                                )
                            )
                            .new Standard(
                                'Nein', __NAMESPACE__, new Disable()
                            )
                        ),
                    )))))
                );
            } else {

                $AccountList = array();
                $UserAccountList = array();
                //Service delete complete Account

                foreach ($tblUserAccountList as $tblUserAccount) {
                    if ($tblUserAccount) {
                        $tblAccount = $tblUserAccount->getServiceTblAccount();
                        if ($tblAccount) {
                            // remove tblAccount
                            if (!AccountAuthorization::useService()->destroyAccount($tblAccount)) {
                                $AccountList[] = $tblAccount;
                            }
                        }
                        // remove tblUserAccount
                        if (!Account::useService()->removeUserAccount($tblUserAccount)) {
                            $UserAccountList[] = $tblUserAccount;
                        }
                    }
                }


                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (empty($AccountList) && empty($UserAccountList)
                                ? new Success(new SuccessIcon().' Die Accounts wurden erfolgreich gelöscht')
                                .new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Remove().' Die Angezeigten Accounts konnten nicht gelöscht werden.')
                                .new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        new TableData($AccountList, new Title('Account'), array(
                                            'Id'                 => 'Id',
                                            'Username'           => 'Benutzer',
                                            'serviceTblConsumer' => 'Consumer Id',
                                        ))
                                        , 6),
                                    new LayoutColumn(
                                        new TableData($UserAccountList, new Title('UserAccount')
                                            , array(
                                                'Id'                => 'Id',
                                                'serviceTblAccount' => 'Account Id',
                                                'EntityCreate'      => 'Erstellungsdatum',
                                                'EntityUpdate'      => 'Letztes Update',
                                            ))
                                        , 6),
                                ))))
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Es konnten keine Accounts gefunden werden'),
                        new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @deprecated Diente zur Datenübertragung alte -> neue DB Struktur Quellcode als Referenz übrig gelassen
     * @return Stage
     */
    public function frontendTransferOltIntegration()
    {

        $Stage = new Stage('Integartion', 'Übernehmen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
        $countPerson = 0;
        $countRequest = 0;
        $countCounsel = 0;
        $countDecision = 0;
        $countPersonWithDisorder = 0;
        if(($tblStudentAll = Student::useService()->getStudentAll())){
            foreach($tblStudentAll as $tblStudent){
                $tblPerson = $tblStudent->getServiceTblPerson();
                if(($tblStudentIntegration = $tblStudent->getTblStudentIntegration())){
                    $Request = $tblStudentIntegration->getCoachingRequestDate();
                    $Counsel = $tblStudentIntegration->getCoachingCounselDate();
                    $Decision = $tblStudentIntegration->getCoachingDecisionDate();

                    if($tblPerson && ($Request || $Counsel || $Decision)){
                        $countPerson++;
                        $Date = false;
                        if($Request){
                            $Request = new \DateTime($Request);
                            $Request = $this->CheckAndCorrectDate($Request);
                            $Date = $Request;
                            $Date = $this->CheckAndCorrectDate($Date);
                            $tblSupportType = Student::useService()->getSupportTypeByName('Förderantrag');
                            $Company = '';
                            if(($tblCompany = $tblStudentIntegration->getServiceTblCompany())){
                                $Company = $tblCompany->getDisplayName();
                            }
                            $PersonSupport = '';
                            $SupportTime = $tblStudentIntegration->getCoachingTime();
                            $Remark = $tblStudentIntegration->getCoachingRemark();
                            $PersonEditor = '';

                            $tblSupport = Student::useService()->importSupport($tblPerson, $tblSupportType, $Request->format('d.m.Y'), $Company, $PersonSupport, $SupportTime, $PersonEditor,$Remark);
                            if($tblSupport){
                                $countRequest++;
                                if(($tblStudentFocusList = Student::useService()->getStudentFocusAllByStudent($tblStudent))){
                                    foreach($tblStudentFocusList as $tblStudentFocus){
                                        $FocusName = $tblStudentFocus->getTblStudentFocusType()->getName();
                                        if(($tblSupportFocusType = Student::useService()->getSupportFocusTypeByName($FocusName))){
                                            Student::useService()->createSupportFocus($tblSupport, $tblSupportFocusType, $tblStudentFocus->isPrimary());
                                        }
                                    }
                                }
                            }
                        }
                        if($Counsel){
                            $Counsel = new \DateTime($Counsel);
                            $Counsel = $this->CheckAndCorrectDate($Counsel);
                            if(!$Date){
                                $Date = $Counsel;
                            } else {
                                if($Date <= $Counsel){
                                    $Date = $Counsel;
                                }
                            }

                            $tblSupportType = Student::useService()->getSupportTypeByName('Beratung');
                            $Company = '';
                            if(($tblCompany = $tblStudentIntegration->getServiceTblCompany())){
                                $Company = $tblCompany->getDisplayName();
                            }
                            $PersonSupport = '';
                            $SupportTime = $tblStudentIntegration->getCoachingTime();
                            $Remark = $tblStudentIntegration->getCoachingRemark();
                            $PersonEditor = '';

                            $tblSupport = Student::useService()->importSupport($tblPerson, $tblSupportType, $Counsel->format('d.m.Y'), $Company, $PersonSupport, $SupportTime, $PersonEditor,$Remark);
                            if($tblSupport){
                                $countCounsel++;
                                if(($tblStudentFocusList = Student::useService()->getStudentFocusAllByStudent($tblStudent))){
                                    foreach($tblStudentFocusList as $tblStudentFocus){
                                        $FocusName = $tblStudentFocus->getTblStudentFocusType()->getName();
                                        if(($tblSupportFocusType = Student::useService()->getSupportFocusTypeByName($FocusName))){
                                            Student::useService()->createSupportFocus($tblSupport, $tblSupportFocusType, $tblStudentFocus->isPrimary());
                                        }
                                    }
                                }
                            }
                        }
                        if($Decision){
                            $Decision = new \DateTime($Decision);
                            $Decision = $this->CheckAndCorrectDate($Decision);
                            if(!$Date){
                                $Date = $Decision;
                            } else {
                                if($Date <= $Decision){
                                    $Date = $Decision;
                                }
                            }

                            $tblSupportType = Student::useService()->getSupportTypeByName('Förderbescheid');
                            $Company = '';
                            if(($tblCompany = $tblStudentIntegration->getServiceTblCompany())){
                                $Company = $tblCompany->getDisplayName();
                            }
                            $PersonSupport = '';
                            $SupportTime = $tblStudentIntegration->getCoachingTime();
                            $Remark = $tblStudentIntegration->getCoachingRemark();
                            $PersonEditor = '';

                            $tblSupport = Student::useService()->importSupport($tblPerson, $tblSupportType, $Decision->format('d.m.Y'), $Company, $PersonSupport, $SupportTime, $PersonEditor,$Remark);
                            if($tblSupport){
                                $countDecision++;
                                if(($tblStudentFocusList = Student::useService()->getStudentFocusAllByStudent($tblStudent))){
                                    foreach($tblStudentFocusList as $tblStudentFocus){
                                        $FocusName = $tblStudentFocus->getTblStudentFocusType()->getName();
                                        if(($tblSupportFocusType = Student::useService()->getSupportFocusTypeByName($FocusName))){
                                            Student::useService()->createSupportFocus($tblSupport, $tblSupportFocusType, $tblStudentFocus->isPrimary());
                                        }
                                    }
                                }
                            }
                        }
                        if($Date){
                            if(($tblStudentDisorderList = Student::useService()->getStudentDisorderAllByStudent($tblStudent))){
                                $PersonEditor = '';
                                $Remark = '';
                                $tblSpecial = Student::useService()->importSpecial($tblPerson, $Date->format('d.m.Y'), $PersonEditor, $Remark);
                                if($tblSpecial){
                                    $countPersonWithDisorder++;
                                    foreach($tblStudentDisorderList as $tblStudentDisorder){
                                        $DisorderName = $tblStudentDisorder->getTblStudentDisorderType()->getName();
                                        if($DisorderName == 'Gehörschwierigkeiten'){
                                            $DisorderName = 'Auditive Wahrnehmungsstörungen';
                                        }
                                        if($DisorderName == 'LRS'){
                                            $DisorderName = 'Lese-/ Rechtschreibstörung';
                                        }
                                        if($DisorderName == 'Dyskalkulie'){
                                            $DisorderName = 'Rechenschwäche';
                                        }
                                        if($DisorderName == 'Hochbegabung'){
                                            $DisorderName = 'Sonstige Entwicklungsbesonderheiten';
                                        }
                                        if($DisorderName == 'Sprachfehler'){
                                            $DisorderName = 'Sprach-/ Sprechstörungen';
                                        }
                                        if($DisorderName == 'Autismus'){
                                            $DisorderName = 'Störungen aus dem Autismusspektrum';
                                        }
                                        if($DisorderName == 'Körperliche Beeinträchtigung'){
                                            $DisorderName = 'Störung motorischer Funktionen';
                                        }
                                        if($DisorderName == 'Augenleiden'){
                                            $DisorderName = 'Visuelle Wahrnehmungsstörungen';
                                        }
                                        if($tblSpecial && ($tblSpecialDisorderType = Student::useService()->getSpecialDisorderTypeByName($DisorderName))){
                                            Student::useService()->createSpecialDisorder($tblSpecial, $tblSpecialDisorderType);
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Es wurden Integrationen für '.$countPerson.' Personen angelegt.')
                    ),
                    new LayoutColumn(
                        ($countRequest == 0
                        ? new Warning('Dabei wurden '.$countRequest.' Förderanträge angelegt.')
                        : new Success('Dabei wurden '.$countRequest.' Förderanträge angelegt.')
                        )

                    ),
                    new LayoutColumn(
                        ($countCounsel == 0
                            ? new Warning('Dabei wurden '.$countCounsel.' Beratungen angelegt.')
                            : new Success('Dabei wurden '.$countCounsel.' Beratungen angelegt.')
                        )
                    ),
                    new LayoutColumn(
                        ($countDecision == 0
                            ? new Warning('Dabei wurden '.$countDecision.' Förderbescheide angelegt.')
                            : new Success('Dabei wurden '.$countDecision.' Förderbescheide angelegt.')
                        )
                    ),
                    new LayoutColumn(
                        ($countPersonWithDisorder == 0
                            ? new Warning('Es wurden zu diesen Personen auch '.$countPersonWithDisorder.' Entwicklungsbesonderheiten hinzugefügt.')
                            : new Success('Es wurden zu diesen Personen auch '.$countPersonWithDisorder.' Entwicklungsbesonderheiten hinzugefügt.')
                        )

                    ),
                ))
            )
        ));

        return $Stage;
    }

    private function CheckAndCorrectDate(\DateTime $Date)
    {

        if($Date <= new \DateTime('01.01.2000')){
            return new \DateTime($Date->format('d.m.').'20'.$Date->format('y'));
        }
        return $Date;
    }

    /**
     * @param array|null $Data
     *
     * @return Stage
     */
    public function frontendGrade(?array $Data = null): Stage
    {
        $stage = new Stage('Datenpflege', 'Zensuren verschieben');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = Term::useService()->getYearAll();

        $stage->setContent(
            new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Source',
                                array(
                                    (new SelectBox('Data[Source][YearId]', 'Schuljahr',
                                        array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                                        ->ajaxPipelineOnChange(array(
                                            ApiGradeMaintenance::pipelineLoadDivisionSelect($Data, 'Source')
                                        ))->setRequired(),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSubjectSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSubjectInformation')
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new LayoutColumn(
                            new Panel(
                                'Target',
                                array(
                                    (new SelectBox('Data[Target][YearId]', 'Schuljahr',
                                        array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                                        ->ajaxPipelineOnChange(array(
                                                ApiGradeMaintenance::pipelineLoadDivisionSelect($Data, 'Target'))
                                        )->setRequired(),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSubjectSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSubjectInformation')
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(ApiGradeMaintenance::receiverBlock(ApiGradeMaintenance::loadMoveButton($Data), 'MoveButton'))
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('&nbsp;'),
                        new LayoutColumn(ApiGradeMaintenance::receiverBlock('', 'OutputInformation'))
                    )),
                )))
            ))))
        );

        return $stage;
    }

    /**
     * @param array|null $Data
     *
     * @return Stage
     */
    public function frontendGradeUnreachable(?array $Data = null): Stage
    {
        $stage = new Stage('Unerreichbare Zensuren');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = Term::useService()->getYearAll();

        $stage->setContent(
            (new SelectBox('Data[YearId]', 'Schuljahr',
                array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                ->ajaxPipelineOnChange(array(
                    ApiGradeMaintenance::pipelineLoadUnreachableGrades($Data)
                ))->setRequired()
            . ApiGradeMaintenance::receiverBlock('', 'UnreachableGrades')
        );

        return  $stage;
    }
}