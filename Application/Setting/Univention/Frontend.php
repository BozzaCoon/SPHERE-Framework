<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Setting\Univention\ApiUnivention;
use SPHERE\Application\Api\Setting\Univention\ApiWorkGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\UniventionTransfer\Service\Entity\TblUniventionAccount;
use SPHERE\Application\Setting\UniventionTransfer\UniventionTransfer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary as PrimaryForm;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Primary as PrimaryText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Univention
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendUnivention()
    {
        $Stage = new Stage('DLLP', '');

        // dynamsiche Rollenliste
        $roleList = (new UniventionRole())->getAllRoles();
        // Fehlerausgabe
        if(Univention::useFrontend()->errorScan($Stage, $roleList)){
            return $Stage;
        }
        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
        // Fehlerausgabe
        if($this->errorScan($Stage, $schoolList)){
            return $Stage;
        }

        // early break if no answer
        if(!is_array($roleList) || !is_array($schoolList)){
            $Stage->setContent(new Warning('DLLP liefert keine Informationen'));
            return $Stage;
        }
        $Acronym = Account::useService()->getMandantAcronym();
        // Mandant ist nicht in der Schulliste
        if( !array_key_exists($Acronym, $schoolList)){
//                if(!in_array($Acronym, $excludeList)){
            $Stage->setContent(new Warning('Ihr Schulträger ist noch nicht in DLLP freigeschalten'));
            return $Stage;
        }

        $isReloadAnnouncement = true;
        $CreateString = 'noch nie';
        $isautoload = true;
        if(($tblUniventionAccount = UniventionTransfer::useService()->getUniventionAccount())){
            $CreateDate = $tblUniventionAccount->getEntityCreate();
            $CreateString = $CreateDate->format('d.m.Y H:i:s');
            $isReloadAnnouncement = ($CreateDate <= (new \DateTime('-5 days')));
            $isautoload = ($CreateDate <= (new \DateTime('-1 days')));
        }
        if($isautoload){
            return $this->frontendUniventionLoad();
        }

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        ($isReloadAnnouncement
                            ? new Warning(new Standard('Manuell laden der DLLP Daten', '/Setting/Univention/Load').'&nbsp;&nbsp;Geladene Daten vom DLLP stand '.new Bold($CreateString)
                                .' Manuelles Laden empfohlen')
                            : new Info(new Standard('Manuell laden der DLLP Daten', '/Setting/Univention/Load').'&nbsp;&nbsp;Abgerufene Daten von DLLP sind von '.new Bold($CreateString)
                                .' ein reload wird bei Anpassungen nach diesem Zeitpunkt empfohlen'))
                    ))
                ,
//                    new LayoutColumn('', 3),
                    new LayoutColumn(
                        new Panel('Voraussetzungen:',
                        new Info(new Bold('Schüler:')
                            .
                            '<ul>
                                <li>'.new Bold('Personengruppe').' Schüler</li>
                                <ul>
                                    <li>Personen => Schüler Datenblatt => Grunddaten</li>
                                    <li>Schüler muss sich in der '.new Bold('Personengruppe Schüler').' befinden</li>
                                </ul>
                                <li>'.new Bold('Klasse').', Schulart, Schule</li>
                                <ul>
                                    <li>Bildung => Unterricht => Kurs</li>
                                    <li>Schüler muss im aktuellen/ausgewähltem Schuljahr einer Klasse, Schulart und Schule zugeordnet sein</li>
                                </ul>
                                <li>'.new Bold('Benutzeraccounts').' anlegen</li>
                                <ul>
                                    <li>Einstellungen => Schüler und Eltern Zugang</li>
                                    <li>Ist dann der Login-Benutzername für das DLLP</li>
                                </ul>
                                <li>Schulische '.new Bold('E-Mail-Adressen').'</li>
                                <ul>
                                    <li>Personen => Schüler Datenblatt => E-Mail-Adressen</li>
                                    <li>Pflichtfeld kann durch den Support für Schüler pro Schulart deaktiviert werden</li>
                                </ul>
                                <li>Passwort zurücksetzen '.new Bold('E-Mail-Adressen').'</li>
                                <ul>
                                    <li>Personen => Schüler Datenblatt => E-Mail-Adressen</li>
                                    <li>Optional für Schüler</li>
                                    <li>'.new WarningText(new WarningIcon()).' Besonderheit bei OX und MS365</li>
                                </ul>
                                <li>'.new Bold('Dienststellenschlüssel (DISCH)').'</li>
                                <ul>
                                    <li>Einstellungen => Mandant => Schulen</li>
                                    <li>Automatische Zuordnung DISCH zu Schüler über deren Schule</li>
                                    <li>'.new WarningText(new WarningIcon()).' Besitzt ein Schulträger mehrere Schulen (mehrere DISCH) und ist bei einem Schüler keine Schule hinterlegt,
                                     wird eine beliebige der hinterlegten DISCH verwendet</li>
                                 </ul>
                             </ul>')
                            .new Info(new Bold('Mitarbeiter / Lehrer:')
                             .'<ul>
                                <li>'.new Bold('Personengruppe').' Mitarbeiter / Lehrer</li>
                                <ul>
                                    <li>Personen => Person Datenblatt => Grunddaten</li>
                                    <li>Person muss sich in der Personengruppe Mitarbeiter befinden und kann zusätzlich auch in der Personengruppe Lehrer sein</li>
                                </ul>
                                <li>'.new Bold('Lehraufträge').'</li>
                                <ul>
                                    <li>Bildung => Unterricht => Lehrauftrag</li>
                                    <li>Für die Übertragung ins DLLP wird nur die Klasse aber nicht das Fach verwendet</li>
                                </ul>
                                <li>'.new Bold('Benutzeraccounts').' anlegen</li>
                                <ul>
                                    <li>Einstellungen => Benutzerverwaltung => Benutzerkonten</li>
                                    <li>Ist dann der Login-Benutzername für das DLLP</li>
                                    <li>Benutzeraccounts dürfen keine Umlaute enthalten (Prüfung bestehender Accounts)</li>
                                </ul>
                                <li>Schulische '.new Bold('E-Mail-Adressen').'</li>
                                <ul>
                                    <li>Personen => Person Datenblatt => E-Mail-Adressen</li>
                                    <li>Pflichtfeld für Mitarbeiter und Lehrer</li>
                                </ul>
                                <li>Passwort zurücksetzen '.new Bold('E-Mail-Adressen').'</li>
                                <ul>
                                    <li>Personen => Person Datenblatt => E-Mail-Adressen</li>
                                    <li>Optional für Mitarbeiter und Lehrer</li>
                                    <li>'.new WarningText(new WarningIcon()).' Besonderheit bei OX und MS365</li>
                                </ul>
                                <li>'.new Bold('Dienststellenschlüssel (DISCH)').'</li>
                                <ul>
                                    <li>Einstellungen => Mandant => Schulen</li>
                                    <li>Automatische Zuordnung DISCH zu Lehrer über deren Lehraufträge</li>
                                    <li>'.new WarningText(new WarningIcon()).' Besitzt ein Schulträger mehrere Schulen (mehrere DISCH) und ist bei einem Lehrer kein Lehrauftrag
                                     hinterlegt bzw. handelt es sich nur um einen Mitarbeiter, wird eine beliebige der hinterlegten DISCH verwendet</li>
                                 </ul>
                            </ul>')
                        , Panel::PANEL_TYPE_DEFAULT)
                    , 10),
                    new LayoutColumn(new Container(new Bold('Detailliertere Informationen:')), 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Schnittstelle Schulsoftware zu DLLP'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'SSW_DLLP')
                        )
                    , 2),
                ))
            )))
        );

        return $Stage;
    }

    /**
     * @param $isWorking
     * @return Stage
     */
    public function frontendUniventionLoad($isWorking = false)
    {

        $Stage = new Stage('DLLP', 'Laden der Daten');

        if(!$isWorking){
            $Stage->setContent(new Info(new Title('Lädt Daten, bitte haben Sie etwas Geduld...')
                .(new ProgressBar(0, 100, 0))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)->setSize('10px')
                .new RedirectScript('/Setting/Univention/Load', 0, array('isWorking' => true))));
        } else {
            UniventionTransfer::useService()->createIndiwareStudentSubjectOrderBulk();
            $Stage->setContent(
                new Success('Aktuelle Daten aus DLLP geladen')
                .new RedirectScript('/Setting/Univention', Redirect::TIMEOUT_SUCCESS));
        }
        return $Stage;
    }

    /**
     * @param $YearId
     * @param $Role
     * @return array
     */
    public function getApiButtons($YearId, $Role)
    {

        // Buttons Default alle gesperrt
        $ButtonCreate = (new Standard('Benutzer anlegen', '', new Plus()))->setDisabled();
        $ButtonUpdate = (new Standard('Benutzer anpassen', '', new Edit()))->setDisabled();
        $ButtonDelete = (new Standard('Benutzer löschen', '', new Remove()))->setDisabled();
        if(($tblConsumer = Consumer::useService()->getConsumerBySession())
            && ($tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP))
        ){
            // Settings freigeschalten
            if($tblConsumerLogin->getIsActiveAPI()){
                //ToDO Typ der Accounts mitgeben (für zurück button)
                $ButtonCreate = new Primary('Benutzer anlegen', '/Setting/Univention/ApiUserSelect', new Plus(), array('Upload' => 'Create', 'YearId' => $YearId, 'Role' => $Role));
                $ButtonUpdate = new Primary('Benutzer anpassen', '/Setting/Univention/ApiUserSelect', new Edit(), array('Upload' => 'Update', 'YearId' => $YearId, 'Role' => $Role));
                $ButtonDelete = new Danger('Benutzer löschen', '/Setting/Univention/ApiUserSelect', new Remove(), array('Upload' => 'Delete', 'YearId' => $YearId, 'Role' => $Role));
            }
        }
        return array($ButtonCreate, $ButtonUpdate, $ButtonDelete);
    }

    /**
     * @param $YearId
     * @return Stage
     */
    public function frontendUniventionTeacher($YearId = '')
    {
        set_time_limit(900);
        $Stage = new Stage('DLLP', 'Schnittstelle API Lehrer und Mitarbeiter');
        $tblUniventionAccountList = array();
        $UserUniventionList = array();
        $Service = UniventionTransfer::useService();
        // Stuff Teacher
        if(($tblUniventionAccountListTemp = $Service->getUniventionAccountListTeacherAndStuff())){
            $tblUniventionAccountList = array_merge($tblUniventionAccountList, $tblUniventionAccountListTemp);
        }
        if($tblUniventionAccountList){
            $tblUniventionAccountList = array_unique($tblUniventionAccountList);
            $UserUniventionList = $Service->convertToArray($tblUniventionAccountList);
        }
        // ApiButtons
        list($ButtonCreate, $ButtonUpdate, $ButtonDelete) = $this->getApiButtons($YearId, TblUniventionAccount::VALUE_TEACHER);

        $YearString = '&nbsp;Aktuelles SJ';
        if($YearId == ''){
            $YearString = new PrimaryText(new Bold($YearString));
        }
        $Stage->addButton(new Standard($YearString, '/Setting/Univention/ApiTeacherStaff', new GroupIcon(), array('YearId' => '')));
        if($nextYearList = Term::useService()->getYearAllFutureYears(1)){
            foreach($nextYearList as $nextYear){
                $YearString = '&nbsp;'.$nextYear->getDisplayName();
                if($YearId == $nextYear->getId()){
                    $YearString = new PrimaryText(new Bold($YearString));
                }
                $Stage->addButton(new Standard($YearString, '/Setting/Univention/ApiTeacherStaff', new GroupIcon(), array('YearId' => $nextYear->getId())));
            }
        }
        $UserSchulsoftwareList = array();
        // Vorraussetzung, es muss ein aktives Schuljahr geben.
        $tblYearList = Term::useService()->getYearByNow();
        if($tblYearList){
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($YearId, TblUniventionAccount::VALUE_TEACHER);
        }

        $UserSchulsoftwareList = array_filter($UserSchulsoftwareList);

        // Prüfung der Accounts (was soll mit welchem Account gemacht werden)
        list($createList, $cantCreateList, $deepSearchList, $cantUpdateList, $deleteList) = Univention::useService()->getCompareUserList($UserSchulsoftwareList, $UserUniventionList);
        $count['create'] = count($createList);
        $count['cantCreate'] = count($cantCreateList);
        $count['cantUpdate'] = count($cantUpdateList);
        $count['delete'] = count($deleteList);
        // Einstellen welche Felder verglichen werden sollen:
        $keyToCompareList = array(
            'firstname' => '',
            'lastname' => '',
            'mail' => '',
            'role' => '',
//            'schools' => '',
            'school_classes' => '',
            'recoveryMail' => '',
            'schoolCode' => '',
//            'guardians' => '',
//            'guardianList' => '',
        );
        list($OkList, $updateList) = Univention::useService()->getOkAndUpdateList($deepSearchList, $UserUniventionList, $keyToCompareList);
        $count['update'] = count($updateList);
        $count['countOK'] = count($OkList);

        $CompareTable = array();
        foreach($updateList as $AccountActive){
            $ExistUser = $UserUniventionList[$AccountActive['record_uid']];
            $CompareRow = array(
                'User' => $AccountActive['name'],
                'DLLP' => $keyToCompareList,
                'SSW' => $keyToCompareList,
                // SSWCopy from function
            );
            $CompareRow = Univention::useService()->fillCompareRow($CompareRow, $ExistUser, $AccountActive);
            $CompareRow = $this->getCompareTable($CompareRow, $keyToCompareList);
            $CompareTable[] = $CompareRow;
        }


        $OkTable = array();
        foreach($OkList as $AccountActive){
            $OkRow = array(
                'User' => $AccountActive['name'],
                'SSW' => $keyToCompareList,
            );
            $OkRow = Univention::useService()->fillOkRow($OkRow, $AccountActive, $keyToCompareList);
            $OkRow = $this->getOkLayout($OkRow, $keyToCompareList);
            $OkTable[] = $OkRow;
        }

        // Frontend Anzeige
        $ContentCreate = array();
//        $ContentUpdate = array();
        $ContentDelete = array();
        if(!empty($createList)){
            foreach($createList as $AccountArray) {
                $ContentCreate[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        if(!empty($updateList)){
            foreach($updateList as $AccountArray) {
                if(isset($AccountArray['UpdateLog'])){
                    $ContentUpdate[] = (new ToolTip($AccountArray['name'].' '.new InfoIcon(), htmlspecialchars(
                        implode('<br/>', $AccountArray['UpdateLog'])
                    )))->enableHtml();
                } else {
                    $ContentUpdate[] = $AccountArray['name'];
                }
            }
        }
        if(!empty($deleteList)){
            foreach($deleteList as $AccountArray) {
                $ContentDelete[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        // Frontend Anzeige Error/Warnung
        $CantCreatePanelContent = array();
        $CantUpdatePanelContent = array();
        if(!empty($cantCreateList)){
            foreach($cantCreateList as $cantCreateAccount){
                $CantCreatePanelContent[] = implode('<br/>', $cantCreateAccount);
            }
        }
        if(!empty($cantUpdateList)){
            foreach($cantUpdateList as $cantUpdateAccount){
                $CantUpdatePanelContent[] = implode('<br/>', $cantUpdateAccount);
            }
        }

        $AccordionCreate = new Accordion();
        $AccordionCreate->addItem('Benutzer die nicht in DLLP angelegt werden können ('.$count['cantCreate'].')',
            '<br/><br/>'.
            new Listing($CantCreatePanelContent)
        );
        $AccordionCreate->addItem('Benutzer für DLLP anlegen ('.$count['create'].')',
            new Listing($ContentCreate)
        );

        $AccordionDelete = new Accordion();
        $AccordionDelete->addItem('Benutzer in DLLP entfernen ('.$count['delete'].')',
            new Listing($ContentDelete)
        );

        $AccordionUpdate = new Accordion();
        $AccordionUpdate->addItem('Benutzer die nicht in DLLP angepasst werden können ('.$count['cantUpdate'].')',
            '<br/><br/>'.
            new Listing($CantUpdatePanelContent)
        );
        $AccordionUpdate->addItem('Benutzer anpassen ('.$count['update'].')',
            new TableData($CompareTable, null, array(
                'User' => 'Benutzer',
                'DLLP' => 'Daten aus DLLP',
                'SSW' => 'Daten aus SSW',
                'SSWCopy' => 'Daten Ergebnis',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '30%', 'targets' => array(1,2,3)),
                ),
                'fixedHeader' => false
            ))
            , true
        );

        $AccordionUntouched = new Accordion();
        $AccordionUntouched->addItem('Benutzer unverändert ('.$count['countOK'].')',
            new TableData($OkTable, null, array(
                'User' => 'Benutzer',
                'SSW' => 'Daten von der SSW sind in DLLP aktuell',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '90%', 'targets' => array(1)),
                ),
                'fixedHeader' => false
            ))
            , false
        );


        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Übersicht',
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new SuccessText('('.$count['cantCreate'].') Benutzer, die nicht angelegt werden können').'<br/>'.
                                    new SuccessText('('.$count['create'].') Benutzer für DLLP anlegen')
                                    , 3),
                                new LayoutColumn(
                                    new DangerText('('.$count['delete'].') Benutzer in DLLP entfernen')
                                    , 3),
                                new LayoutColumn(
                                    new InfoText('('.$count['cantUpdate'].') Benutzer, die nicht angepasst werden können').'<br/>'.
                                    new InfoText('('.$count['update'].') Benutzer anpassen') // ' von '.$count['allUpdate'].
                                    , 3),
                                new LayoutColumn(
                                    '('.$count['countOK'].') Benutzer unverändert'
                                    , 3),
                            ))
                        ))
                        , Panel::PANEL_TYPE_INFO
                    )
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new SuccessText(new Plus().' Anlegen').new PullRight($ButtonCreate)))
                        .$AccordionCreate)
                    , 6),
                new LayoutColumn(
                    new Well(new Title(new PullClear(new DangerText(new Remove().' Löschen').new PullRight($ButtonDelete)))
                        .$AccordionDelete)
                    , 6)
            )),
            new LayoutRow(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new InfoText(new Edit().' Anpassen').new PullRight($ButtonUpdate)))
                        .$AccordionUpdate)
                )
            ),
            new LayoutRow(
                new LayoutColumn(
                    new Well($AccordionUntouched)
                )
            ),
        ))));

        return $Stage;
    }

    /**
     * @param $YearId
     * @return Stage
     */
    public function frontendUniventionGuardian($YearId = '')
    {
        set_time_limit(900);
        $Stage = new Stage('DLLP', 'Schnittstelle API Sorgeberechtigte');
        $tblUniventionAccountList = array();
        $UserUniventionList = array();
        $Service = UniventionTransfer::useService();
        // Guardian
        if(($tblUniventionAccountListTemp = $Service->getUniventionAccountListByRoleLike(TblUniventionAccount::VALUE_GUARDIAN))){
            $tblUniventionAccountList = array_merge($tblUniventionAccountList, $tblUniventionAccountListTemp);
        }
        if($tblUniventionAccountList){
            $tblUniventionAccountList = array_unique($tblUniventionAccountList);
            $UserUniventionList = $Service->convertToArray($tblUniventionAccountList);
        }
        // ApiButtons
        list($ButtonCreate, $ButtonUpdate, $ButtonDelete) = $this->getApiButtons($YearId, TblUniventionAccount::VALUE_GUARDIAN);

        // Jahr wird für Sorgeberechtigte nicht benötigt
//        $YearString = '&nbsp;Aktuelles SJ';
//        if($YearId == ''){
//            $YearString = new PrimaryText(new Bold($YearString));
//        }
//        $Stage->addButton(new Standard($YearString, '/Setting/Univention/Api', new GroupIcon(), array('YearId' => '')));
//        if($nextYearList = Term::useService()->getYearAllFutureYears(1)){
//            foreach($nextYearList as $nextYear){
//                $YearString = '&nbsp;'.$nextYear->getDisplayName();
//                if($YearId == $nextYear->getId()){
//                    $YearString = new PrimaryText(new Bold($YearString));
//                }
//                $Stage->addButton(new Standard($YearString, '/Setting/Univention/ApiStudent', new GroupIcon(), array('YearId' => $nextYear->getId())));
//            }
//        }
        $UserSchulsoftwareList = array();
        // Vorraussetzung, es muss ein aktives Schuljahr geben.
        $tblYearList = Term::useService()->getYearByNow();
        if($tblYearList){
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($YearId, TblUniventionAccount::VALUE_GUARDIAN);
        }

        $UserSchulsoftwareList = array_filter($UserSchulsoftwareList);

        // Prüfung der Accounts (was soll mit welchem Account gemacht werden)
        list($createList, $cantCreateList, $deepSearchList, $cantUpdateList, $deleteList) = Univention::useService()->getCompareUserList($UserSchulsoftwareList, $UserUniventionList);
        $count['create'] = count($createList);
        $count['cantCreate'] = count($cantCreateList);
        $count['cantUpdate'] = count($cantUpdateList);
        $count['delete'] = count($deleteList);
        // Einstellen welche Felder verglichen werden sollen:
        $keyToCompareList = array(
            'firstname' => '',
            'lastname' => '',
            'mail' => '',
            'role' => '',
//            'schools' => '',
            'school_classes' => '',
            'recoveryMail' => '',
            'schoolCode' => '',
//            'guardians' => '',
//            'guardianList' => '',
        );
        list($OkList, $updateList) = Univention::useService()->getOkAndUpdateList($deepSearchList, $UserUniventionList, $keyToCompareList);
        $count['update'] = count($updateList);
        $count['countOK'] = count($OkList);


        $CompareTable = array();
        foreach($updateList as $AccountActive){
            $ExistUser = $UserUniventionList[$AccountActive['record_uid']];
            $CompareRow = array(
                'User' => $AccountActive['name'],
                'DLLP' => $keyToCompareList,
                'SSW' => $keyToCompareList,
                // SSWCopy from function
            );
            $CompareRow = Univention::useService()->fillCompareRow($CompareRow, $ExistUser, $AccountActive);
            $CompareRow = $this->getCompareTable($CompareRow, $keyToCompareList);
            $CompareTable[] = $CompareRow;
        }


        $OkTable = array();
        foreach($OkList as $AccountActive){
            $OkRow = array(
                'User' => $AccountActive['name'],
                'SSW' => $keyToCompareList,
            );
            $OkRow = Univention::useService()->fillOkRow($OkRow, $AccountActive, $keyToCompareList);
            $OkRow = $this->getOkLayout($OkRow, $keyToCompareList);
            $OkTable[] = $OkRow;
        }

        // Frontend Anzeige
        $ContentCreate = array();
//        $ContentUpdate = array();
        $ContentDelete = array();
        if(!empty($createList)){
            foreach($createList as $AccountArray) {
                $ContentCreate[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        if(!empty($updateList)){
            foreach($updateList as $AccountArray) {
                if(isset($AccountArray['UpdateLog'])){
                    $ContentUpdate[] = (new ToolTip($AccountArray['name'].' '.new InfoIcon(), htmlspecialchars(
                        implode('<br/>', $AccountArray['UpdateLog'])
                    )))->enableHtml();
                } else {
                    $ContentUpdate[] = $AccountArray['name'];
                }
            }
        }
        if(!empty($deleteList)){
            foreach($deleteList as $AccountArray) {
                $ContentDelete[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        // Frontend Anzeige Error/Warnung
        $CantCreatePanelContent = array();
        $CantUpdatePanelContent = array();
        if(!empty($cantCreateList)){
            foreach($cantCreateList as $cantCreateAccount){
                $CantCreatePanelContent[] = implode('<br/>', $cantCreateAccount);
            }
        }
        if(!empty($cantUpdateList)){
            foreach($cantUpdateList as $cantUpdateAccount){
                $CantUpdatePanelContent[] = implode('<br/>', $cantUpdateAccount);
            }
        }

        $AccordionCreate = new Accordion();
        $AccordionCreate->addItem('Benutzer die nicht in DLLP angelegt werden können ('.$count['cantCreate'].')',
            '<br/><br/>'.
            new Listing($CantCreatePanelContent)
        );
        $AccordionCreate->addItem('Benutzer für DLLP anlegen ('.$count['create'].')',
            new Listing($ContentCreate)
        );

        $AccordionDelete = new Accordion();
        $AccordionDelete->addItem('Benutzer in DLLP entfernen ('.$count['delete'].')',
            new Listing($ContentDelete)
        );

        $AccordionUpdate = new Accordion();
        $AccordionUpdate->addItem('Benutzer die nicht in DLLP angepasst werden können ('.$count['cantUpdate'].')',
            '<br/><br/>'.
            new Listing($CantUpdatePanelContent)
        );
        $AccordionUpdate->addItem('Benutzer anpassen ('.$count['update'].')',
            new TableData($CompareTable, null, array(
                'User' => 'Benutzer',
                'DLLP' => 'Daten aus DLLP',
                'SSW' => 'Daten aus SSW',
                'SSWCopy' => 'Daten Ergebnis',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '30%', 'targets' => array(1,2,3)),
                ),
                'fixedHeader' => false
            ))
            , true
        );

        $AccordionUntouched = new Accordion();
        $AccordionUntouched->addItem('Benutzer unverändert ('.$count['countOK'].')',
            new TableData($OkTable, null, array(
                'User' => 'Benutzer',
                'SSW' => 'Daten von der SSW sind in DLLP aktuell',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '90%', 'targets' => array(1)),
                ),
                'fixedHeader' => false
            ))
            , false
        );


        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Übersicht',
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new SuccessText('('.$count['cantCreate'].') Benutzer, die nicht angelegt werden können').'<br/>'.
                                    new SuccessText('('.$count['create'].') Benutzer für DLLP anlegen')
                                    , 3),
                                new LayoutColumn(
                                    new DangerText('('.$count['delete'].') Benutzer in DLLP entfernen')
                                    , 3),
                                new LayoutColumn(
                                    new InfoText('('.$count['cantUpdate'].') Benutzer, die nicht angepasst werden können').'<br/>'.
                                    new InfoText('('.$count['update'].') Benutzer anpassen') // ' von '.$count['allUpdate'].
                                    , 3),
                                new LayoutColumn(
                                    '('.$count['countOK'].') Benutzer unverändert'
                                    , 3),
                            ))
                        ))
                        , Panel::PANEL_TYPE_INFO
                    )
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new SuccessText(new Plus().' Anlegen').new PullRight($ButtonCreate)))
                        .$AccordionCreate)
                    , 6),
                new LayoutColumn(
                    new Well(new Title(new PullClear(new DangerText(new Remove().' Löschen').new PullRight($ButtonDelete)))
                        .$AccordionDelete)
                    , 6)
            )),
            new LayoutRow(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new InfoText(new Edit().' Anpassen').new PullRight($ButtonUpdate)))
                        .$AccordionUpdate)
                )
            ),
            new LayoutRow(
                new LayoutColumn(
                    new Well($AccordionUntouched)
                )
            ),
        ))));

        return $Stage;
    }

    /**
     * @param $YearId
     * @return Stage
     */
    public function frontendUniventionStudent($YearId = '')
    {
        set_time_limit(900);
        $Stage = new Stage('DLLP', 'Schnittstelle API Schüler');
        $tblUniventionAccountList = array();
        $UserUniventionList = array();
        $Service = UniventionTransfer::useService();
        // Student
        if(($tblUniventionAccountListTemp = $Service->getUniventionAccountListByRole(TblUniventionAccount::VALUE_STUDENT))){
            $tblUniventionAccountList = array_merge($tblUniventionAccountList, $tblUniventionAccountListTemp);
        }
        if($tblUniventionAccountList){
            $tblUniventionAccountList = array_unique($tblUniventionAccountList);
            $UserUniventionList = $Service->convertToArray($tblUniventionAccountList);
        }
        // ApiButtons
        list($ButtonCreate, $ButtonUpdate, $ButtonDelete) = $this->getApiButtons($YearId, TblUniventionAccount::VALUE_STUDENT);

        $YearString = '&nbsp;Aktuelles SJ';
        if($YearId == ''){
            $YearString = new PrimaryText(new Bold($YearString));
        }
        $Stage->addButton(new Standard($YearString, '/Setting/Univention/ApiStudent', new GroupIcon(), array('YearId' => '')));
        if($nextYearList = Term::useService()->getYearAllFutureYears(1)){
            foreach($nextYearList as $nextYear){
                $YearString = '&nbsp;'.$nextYear->getDisplayName();
                if($YearId == $nextYear->getId()){
                    $YearString = new PrimaryText(new Bold($YearString));
                }
                $Stage->addButton(new Standard($YearString, '/Setting/Univention/ApiStudent', new GroupIcon(), array('YearId' => $nextYear->getId())));
            }
        }
        $UserSchulsoftwareList = array();
        // Vorraussetzung, es muss ein aktives Schuljahr geben.
        $tblYearList = Term::useService()->getYearByNow();
        if($tblYearList){
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($YearId, TblUniventionAccount::VALUE_STUDENT);
        }

        $UserSchulsoftwareList = array_filter($UserSchulsoftwareList);

        // Prüfung der Accounts (was soll mit welchem Account gemacht werden)
        list($createList, $cantCreateList, $deepSearchList, $cantUpdateList, $deleteList) = Univention::useService()->getCompareUserList($UserSchulsoftwareList, $UserUniventionList);
        $count['create'] = count($createList);
        $count['cantCreate'] = count($cantCreateList);
        $count['cantUpdate'] = count($cantUpdateList);
        $count['delete'] = count($deleteList);
        // Einstellen welche Felder verglichen werden sollen:
        $keyToCompareList = array(
            'firstname' => '',
            'lastname' => '',
            'mail' => '',
            'role' => '',
//            'schools' => '',
            'school_classes' => '',
            'recoveryMail' => '',
            'schoolCode' => '',
//            'guardians' => '',
            'guardianList' => '',
        );
        list($OkList, $updateList) = Univention::useService()->getOkAndUpdateList($deepSearchList, $UserUniventionList, $keyToCompareList);
        $count['update'] = count($updateList);
        $count['countOK'] = count($OkList);


        $CompareTable = array();
        foreach($updateList as $AccountActive){
            $ExistUser = $UserUniventionList[$AccountActive['record_uid']];
            $CompareRow = array(
                'User' => $AccountActive['name'],
                'DLLP' => $keyToCompareList,
                'SSW' => $keyToCompareList,
                // SSWCopy from function
            );
            $CompareRow = Univention::useService()->fillCompareRow($CompareRow, $ExistUser, $AccountActive);
            $CompareRow = $this->getCompareTable($CompareRow, $keyToCompareList);
            $CompareTable[] = $CompareRow;
        }


        $OkTable = array();
        foreach($OkList as $AccountActive){
            $OkRow = array(
                'User' => $AccountActive['name'],
                'SSW' => $keyToCompareList,
            );
            $OkRow = Univention::useService()->fillOkRow($OkRow, $AccountActive, $keyToCompareList);
            $OkRow = $this->getOkLayout($OkRow, $keyToCompareList);
            $OkTable[] = $OkRow;
        }

        // Frontend Anzeige
        $ContentCreate = array();
//        $ContentUpdate = array();
        $ContentDelete = array();
        if(!empty($createList)){
            foreach($createList as $AccountArray) {
                $ContentCreate[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        if(!empty($updateList)){
            foreach($updateList as $AccountArray) {
                if(isset($AccountArray['UpdateLog'])){
                    $ContentUpdate[] = (new ToolTip($AccountArray['name'].' '.new InfoIcon(), htmlspecialchars(
                        implode('<br/>', $AccountArray['UpdateLog'])
                    )))->enableHtml();
                } else {
                    $ContentUpdate[] = $AccountArray['name'];
                }
            }
        }
        if(!empty($deleteList)){
            foreach($deleteList as $AccountArray) {
                $ContentDelete[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        // Frontend Anzeige Error/Warnung
        $CantCreatePanelContent = array();
        $CantUpdatePanelContent = array();
        if(!empty($cantCreateList)){
            foreach($cantCreateList as $cantCreateAccount){
                $CantCreatePanelContent[] = implode('<br/>', $cantCreateAccount);
            }
        }
        if(!empty($cantUpdateList)){
            foreach($cantUpdateList as $cantUpdateAccount){
                $CantUpdatePanelContent[] = implode('<br/>', $cantUpdateAccount);
            }
        }

        $AccordionCreate = new Accordion();
        $AccordionCreate->addItem('Benutzer die nicht in DLLP angelegt werden können ('.$count['cantCreate'].')',
            '<br/><br/>'.
            new Listing($CantCreatePanelContent)
        );
        $AccordionCreate->addItem('Benutzer für DLLP anlegen ('.$count['create'].')',
            new Listing($ContentCreate)
        );

        $AccordionDelete = new Accordion();
        $AccordionDelete->addItem('Benutzer in DLLP entfernen ('.$count['delete'].')',
            new Listing($ContentDelete)
        );

        $AccordionUpdate = new Accordion();
        $AccordionUpdate->addItem('Benutzer die nicht in DLLP angepasst werden können ('.$count['cantUpdate'].')',
            '<br/><br/>'.
            new Listing($CantUpdatePanelContent)
        );
        $AccordionUpdate->addItem('Benutzer anpassen ('.$count['update'].')',
            new TableData($CompareTable, null, array(
                'User' => 'Benutzer',
                'DLLP' => 'Daten aus DLLP',
                'SSW' => 'Daten aus SSW',
                'SSWCopy' => 'Daten Ergebnis',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '30%', 'targets' => array(1,2,3)),
                ),
                'fixedHeader' => false
            ))
            , true
        );

        $AccordionUntouched = new Accordion();
        $AccordionUntouched->addItem('Benutzer unverändert ('.$count['countOK'].')',
            new TableData($OkTable, null, array(
                'User' => 'Benutzer',
                'SSW' => 'Daten von der SSW sind in DLLP aktuell',
            ), array(
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '90%', 'targets' => array(1)),
                ),
                'fixedHeader' => false
            ))
            , false
        );


        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Übersicht',
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new SuccessText('('.$count['cantCreate'].') Benutzer, die nicht angelegt werden können').'<br/>'.
                                    new SuccessText('('.$count['create'].') Benutzer für DLLP anlegen')
                                    , 3),
                                new LayoutColumn(
                                    new DangerText('('.$count['delete'].') Benutzer in DLLP entfernen')
                                    , 3),
                                new LayoutColumn(
                                    new InfoText('('.$count['cantUpdate'].') Benutzer, die nicht angepasst werden können').'<br/>'.
                                    new InfoText('('.$count['update'].') Benutzer anpassen') // ' von '.$count['allUpdate'].
                                    , 3),
                                new LayoutColumn(
                                    '('.$count['countOK'].') Benutzer unverändert'
                                    , 3),
                            ))
                        ))
                        , Panel::PANEL_TYPE_INFO
                    )
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new SuccessText(new Plus().' Anlegen').new PullRight($ButtonCreate)))
                        .$AccordionCreate)
                    , 6),
                new LayoutColumn(
                    new Well(new Title(new PullClear(new DangerText(new Remove().' Löschen').new PullRight($ButtonDelete)))
                        .$AccordionDelete)
                    , 6)
            )),
            new LayoutRow(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new InfoText(new Edit().' Anpassen').new PullRight($ButtonUpdate)))
                        .$AccordionUpdate)
                )
            ),
            new LayoutRow(
                new LayoutColumn(
                    new Well($AccordionUntouched)
                )
            ),
        ))));

        return $Stage;
    }

    /**
     * @param $CompareRow
     * @param $keyToCompareList array(
     * 'firstname' => '',
     * 'lastname' => '',
     * 'mail' => '',
     * 'role' => '',
     * 'school_classes' => '',
     * 'recoveryMail' => '',
     * 'schoolCode' => '',
     * 'guardians' => '',
     * 'guardianList' => ''
     * )
     * @return mixed
     */
    public function getCompareTable($CompareRow, $keyToCompareList)
    {
        // Layout in TableContent
        $firstWith = 4;
        $secondWith = 8;
        // Beschriftungen für die Keys
        $labels = [
            'firstname'      => 'Vorname:',
            'lastname'       => 'Nachname:',
            'mail'           => 'E-Mail:',
            'recoveryMail'   => 'E-Mail Recovery:',
            'role'           => 'Rolle:',
            'school_classes' => 'Klassen:',
            'guardians'      => 'Sorgeb.:',
            'guardianList'   => 'Sorgeb. (Liste):',
            'schoolCode'     => 'DISCH:',
        ];

        $CompareRow['DLLP'] = $this->createCompareSection($CompareRow['DLLP'], $keyToCompareList, $labels, $firstWith, $secondWith);
        $CompareRow['SSW']  = $this-> createCompareSection($CompareRow['SSW'],  $keyToCompareList, $labels, $firstWith, $secondWith);
        $CompareRow['SSWCopy'] = $CompareRow['SSW'];
        return $CompareRow;
    }

    private function createCompareSection($compareData, $keyList, $labels, $firstWith, $secondWith)
    {
        $rows = array();

        foreach ($keyList as $key => $value) {
            // Prüfe, ob im Compare-Datensatz vorhanden
            if (!isset($compareData[$key])) continue;

            $label = $labels[$key] ?? ucfirst($key) . ':';

            $rows[] = new LayoutRow(array(
                new LayoutColumn(new Bold($label), $firstWith),
                new LayoutColumn($compareData[$key], $secondWith),
            ));
        }

        return new Small(
            new Layout(new LayoutGroup($rows))
        );
    }

    public function getOkLayout($OkRow, $keyToCompareList)
    {

        //ToDO liste der vergleiche mitgeben
        $firstWith = 2;
        $secondWith = 10;
        $labels = [
            'firstname'      => 'Vorname:',
            'lastname'       => 'Nachname:',
            'mail'           => 'E-Mail:',
            'recoveryMail'   => 'E-Mail Recovery:',
            'role'           => 'Rolle:',
            'school_classes' => 'Klassen:',
            'guardians'      => 'Sorgeb.:',
            'guardianList'   => 'Sorgeb. (Liste):',
            'schoolCode'     => 'DISCH:',
        ];
        $CompareRow['User'] = $OkRow['User'];
        $CompareRow['SSW']  = $this-> createCompareSection($OkRow['SSW'],  $keyToCompareList, $labels, $firstWith, $secondWith);

        return $CompareRow;
    }

    public function frontendUserSelectAPI($userIdentifier = null, $Upload = 'Create', $YearId = '', $Role = TblUniventionAccount::VALUE_STUDENT)
    {

        $SiteType = '';
        switch ($Upload) {
            case 'Create':
                $SiteType = 'erstellen';
                break;
            case 'Update':
                $SiteType = 'aktualisieren';
                break;
            case 'Delete':
                $SiteType = 'löschen';
                break;
        }

        $Stage = new Stage('Benutzerauswahl',$SiteType);
        $Route = '/Setting/Univention';
        switch ($Role) {
            case TblUniventionAccount::VALUE_STUDENT:
                $Route = '/Setting/Univention/ApiStudent';
                break;
            case TblUniventionAccount::VALUE_TEACHER:
                $Route = '/Setting/Univention/ApiTeacherStaff';
                break;
            case TblUniventionAccount::VALUE_GUARDIAN:
                $Route = '/Setting/Univention/ApiGuardian';
                break;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft(), array('YearId' => $YearId)));

        $UserUniventionList = array();
        $UserSchulsoftwareList = array();
        $Service = UniventionTransfer::useService();
        $tblUniventionAccountList = array();
        // role do the trick -> teacher also get stuff
        if(($tblUniventionAccountListTemp = $Service->getUniventionAccountListByRole($Role))){
            $tblUniventionAccountList = array_merge($tblUniventionAccountList, $tblUniventionAccountListTemp);
        }

        if(!empty($tblUniventionAccountList)){
            $tblUniventionAccountList = array_unique($tblUniventionAccountList);
            $UserUniventionList = $Service->convertToArray($tblUniventionAccountList);
        }

        // Vorraussetzung, es muss ein aktives Schuljahr geben.
        $tblYearList = Term::useService()->getYearByNow();
        if($tblYearList){
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($YearId, $Role);
        }
        // Prüfung der Accounts (was soll mit welchem Account gemacht werden)
        list($createList, $cantCreateList, $updateList, $cantUpdateList, $deleteList) = Univention::useService()->getCompareUserList($UserSchulsoftwareList, $UserUniventionList);
        switch($Upload) {
            case 'Create':
                $userList = $createList;
                break;
            case 'Update':
                $userList = $updateList;
                break;
            case 'Delete':
                $userList = $deleteList;
                break;
        }
        $CountUserList = count($userList);

        if(empty($userIdentifier)){

            usort($userList, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $CheckboxList = array();
            foreach($userList as $user){
                $CheckboxList[] = new FormColumn((new CheckBox('userIdentifier['.$user['record_uid'].']',
                    $user['name'].' - '.$user['firstname'].' '.$user['lastname'],
                    $user['record_uid']))->setChecked(), 4);
            }
            $form = new Form(new FormGroup(new FormRow($CheckboxList)), new PrimaryForm('Speichern', new Save()));
            $ToggleButton = new ToggleCheckbox('Alle auswählen/abwählen', $form);
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(new Well(
                        new Title('Anzahl Benutzer: '.$CountUserList.' '.$ToggleButton)
                        .Univention::useService()->transferUserApi($form, $userIdentifier, $Upload, $YearId, $Role)
                        )
                    , 8)
                ))))
            );
            return $Stage;
        }

        foreach($userList as &$user){
            if(!in_array($user['record_uid'], $userIdentifier)){
                $user = false;
            }
        }

        $userList = array_filter($userList);

        $CountMax = count($userList);
        $TypeFrontend = '';
        if($CountMax > 0){

            // avoid max_input_vars
            $UserList = json_encode($userList);
            if($Upload == 'Create'){
                $TypeFrontend = 'Anlegen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $Upload, $CountMax);
            }elseif($Upload == 'Update'){
                $TypeFrontend = 'Bearbeiten von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $Upload, $CountMax);
            }elseif($Upload == 'Delete'){
                $TypeFrontend = 'Löschen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $Upload, $CountMax);
            }

            // insert receiver into frontend
            $LayoutRowAPI = new LayoutRow(new LayoutColumn(ApiUnivention::receiverUser($PipelineServiceUser), 4));
            for($i = 1; $i <= $CountMax; $i++){
                $LayoutRowAPI->addColumn(new LayoutColumn(ApiUnivention::receiverUser('', $i), 4));
            }

            $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn(new Title($TypeFrontend)),
                new LayoutColumn(ApiUnivention::receiverLoad(ApiUnivention::pipelineLoad(0, $CountMax))),
                new LayoutColumn('<div style="height: 15px"> </div>'),
            )),
                $LayoutRowAPI
            ))));
        } else {
            $Stage->setContent(
                new Warning(new Center('Es sind keine Transaktionen verfügbar.'))
            );
        }

        return $Stage;

//        // Upload erst nach ausführlicher Bestätigung
//        if($Upload == 'Create'){
//            return $this->frontendApiAction($userList, $Upload, $YearId);
//        } elseif($Upload == 'Update'){
//            return $this->frontendApiAction($userList, $Upload, $YearId);
//        } elseif($Upload == 'Delete'){
//            return $this->frontendApiAction($userList, $Upload, $YearId);
//        }
    }

    /**
     * @param        $UserList
     * @param string $ApiType
     *
     * @return Stage
     */
    public function frontendApiAction($UserList, $ApiType = '', $YearId = '')
    {

        $Stage = new Stage('API', 'Transfermeldung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention/Api', new ChevronLeft(), array('YearId' => $YearId)));

        $CountMax = count($UserList);
        $TypeFrontend = '';
        if($CountMax > 0){

            // avoid max_input_vars
            $UserList = json_encode($UserList);
            if($ApiType == 'Create'){
                $TypeFrontend = 'Anlegen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }elseif($ApiType == 'Update'){
                $TypeFrontend = 'Bearbeiten von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }elseif($ApiType == 'Delete'){
                $TypeFrontend = 'Löschen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }

            // insert receiver into frontend
            $LayoutRowAPI = new LayoutRow(new LayoutColumn(ApiUnivention::receiverUser($PipelineServiceUser), 4));
            for($i = 1; $i <= $CountMax; $i++){
                $LayoutRowAPI->addColumn(new LayoutColumn(ApiUnivention::receiverUser('', $i), 4));
            }

            $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn(new Title($TypeFrontend)),
                new LayoutColumn(ApiUnivention::receiverLoad(ApiUnivention::pipelineLoad(0, $CountMax))),
                new LayoutColumn('<div style="height: 15px"> </div>'),
            )),
                $LayoutRowAPI
            ))));
        } else {
            $Stage->setContent(
                new Warning(new Center('Es sind keine Transaktionen verfügbar.'))
            );
        }

        return $Stage;
    }

    /**
     * @param bool $isSekII
     * @param bool $isStart
     *
     * @return Stage
     */
    public function frontendWorkGroupAPI(bool $isSekII = false, bool $isStart = false):Stage
    {

        $Stage = new Stage('API', 'Arbeitsgruppen-Abgleich');
        if(!$isStart){
            $DivisionCourseList = $this->getDivisionCourseList(true);
            $ErrorList = array();
            if($DivisionCourseList){
                foreach($DivisionCourseList as $DivisionCourse){
                    $divisionName = $DivisionCourse->getName();
                    $error = $this->isDivisionCourseValid($divisionName);
                    if($error){
                        $TypeName = $DivisionCourse->getTypeName();
                        $ErrorList[$TypeName][] = $error.' '.$divisionName;
                    }
                }
            }
            if(!empty($ErrorList)){
                $LayoutColumnList = array();
                foreach($ErrorList as $TypeName => $ErrorCourseList){
                    $LayoutColumnList[] = new LayoutColumn(new Panel($TypeName, new Listing($ErrorCourseList), Panel::PANEL_TYPE_WARNING), 3);
                }
            }
            $_POST['isStart'] = true;
            $Stage->setContent(new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(new Well( new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(new CheckBox('isSekII', 'auch SEKII-Kurse als Arbeitsgruppen übermitteln', 1), 11),
                            new FormColumn(new HiddenField('isStart'), 1),
                        )),
                        new FormRow(
                            new FormColumn(
                                new PrimaryForm('Datenabgleich der Arbeitsgruppen starten', new Upload())
                                , 2),
                        )
                    )))), 4),
                    new LayoutColumn(new Warning('Diese Schnittstelle legt neue Stammgruppen aus der Schulsoftware als
                     Arbeitsgruppen im DLLP an und ordnet die entsprechenden Schüler / Lehrer (Lehrauftrag)
                     diesen Gruppen zu.'
                    .new Container('Bitte beachten Sie, dass die entsprechenden Schüler / Lehrer zuvor
                     mittels der Schnittstelle '.new Bold('"DLLP über API" erst nach DLLP übertragen').'
                     werden müssen.')
                    ), 8)
                )),
                new LayoutRow((!empty($ErrorList)?$LayoutColumnList : new LayoutColumn('')))
            ))));
            return $Stage;
        }

        $Acronym = Account::useService()->getMandantAcronym();
        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
        // Fehlerausgabe
        if($this->errorScan($Stage, $schoolList)){
            return $Stage;
        }
        // early break if no answer
        if(!is_array($schoolList)){
            $Stage->setContent(new Warning('DLLP liefert keine Informationen'));
            return $Stage;
        }
        // Mandant ist nicht in der Schulliste
        if( !array_key_exists($Acronym, $schoolList)){
            $Stage->setContent(new Warning('Ihr Schulträger ist noch nicht in DLLP freigeschalten'));
            return $Stage;
        }
        $school = $schoolList[$Acronym];
        // Vorhandene Nutzer in Univention holen
        $UserUniventionList = Univention::useService()->getApiUser();
        $ApiUserNameList = array();
        if($UserUniventionList){
            foreach($UserUniventionList as $UserUnivention){
                $ApiUserNameList[] = $UserUnivention['name'];
            }
        }

        $ApiWorkGroupList = (new UniventionWorkGroup())->getWorkGroupListAll();
        $ApiGroupArray = array();
        if($ApiWorkGroupList){
            // Workgroup mit Nutzernamen
            foreach($ApiWorkGroupList as $ApiWorkGroup){
                $group = $ApiWorkGroup['name'];
                if(!empty($ApiWorkGroup['users'])){
                    foreach($ApiWorkGroup['users'] as &$User){
                        // Nutzernamen aus URL
                        $Position = strpos($User, $Acronym.'-');
                        $TempUser = str_split($User, $Position);
                        $User = $TempUser[1];
                    }
                }
                sort($ApiWorkGroup['users']);
                $ApiGroupArray[$group] = $ApiWorkGroup['users'];
            }
        }

        $DivisionCourseList = $this->getDivisionCourseList($isSekII);
        if($DivisionCourseList){
            foreach($DivisionCourseList as $tblDivisionCourse){
                $GroupName = $tblDivisionCourse->getName();
                $tblPersonAccountList = array();
                // Lehrauftrag
                if(($tblYearList = Term::useService()->getYearByNow())){
                    foreach($tblYearList as $tblYear){
                        if(($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse))){
                            foreach($tblTeacherLectureshipList as $tblTeacherLectureship){
                                if(($tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson())){
                                    // Nur Lehrer mit Lehrauftrag und einem Account
                                    if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonTeacher))) {
                                        $tblAccount = current($tblAccountList);
                                        // Nutzer müssen in der API verfügbar sein
                                        if(in_array($tblAccount->getUsername(), $ApiUserNameList)){
                                            $tblPersonAccountList[$tblAccount->getId()] = $tblAccount->getUsername();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
//                // Klassenlehrer / Gruppenleiter -> werden bei der Normalen API auch nicht übertragen, deswegen dagegen entschieden
//                if(($tblPersonDivisionTeacherList = $tblDivisionCourse->getDivisionTeacherList())){
//                    foreach($tblPersonDivisionTeacherList as $tblPersonDivisionTeacher){
//                        if($tblPersonDivisionTeacher){
//                            // Nur Lehrer mit Lehrauftrag und einem Account
//                            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonDivisionTeacher))) {
//                                $tblAccount = current($tblAccountList);
//                                // Nutzer müssen in der API verfügbar sein
//                                if(in_array($tblAccount->getUsername(), $ApiUserNameList)){
//                                    $tblPersonAccountList[$tblAccount->getId()] = $tblAccount->getUsername();
//                                }
//                            }
//                        }
//                    }
//                }

                if(($tblPersonList = $tblDivisionCourse->getStudents())){
                    foreach($tblPersonList as $tblPerson){
                        // Nur Schüler mit einem Account
                        if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                            $tblAccount = current($tblAccountList);
                            // Nutzer müssen in der API verfügbar sein
                            if(in_array($tblAccount->getUsername(), $ApiUserNameList)){
                                $tblPersonAccountList[$tblAccount->getId()] = $tblAccount->getUsername();
                            }
                        }
                    }
                }
                if((array_key_exists($GroupName, $ApiGroupArray))){
                    $ApiUserList = $ApiGroupArray[$GroupName];
                    if(count($ApiUserList) != count($tblPersonAccountList)
                        || ($Diff = array_diff($ApiUserList, $tblPersonAccountList))){
                        // Gruppen SSW & Univention unterscheiden sich
                        $Type = 'update';
                    } else {
                        // sonst keine Änderungen
                        $Type = 'ok';
                    }
                } else {
                    $Type = 'create';
                }
                if($this->isDivisionCourseValid($GroupName)){
                    $Type = 'canNot';
                }
                $ContentArray[$GroupName] = array(
                    'Group' => $GroupName,
                    'UserList' => $tblPersonAccountList,
                    'Type' => $Type,
                    'School' => $school
                );
            }
        }

        if(!empty($ContentArray)){
            ksort($ContentArray);
        }

        $CountMax = count($ContentArray);
        if($CountMax > 0){

            // avoid max_input_vars
            $ContentJson = json_encode($ContentArray);
            $PipelineServiceWorkgroup = ApiWorkGroup::pipelineServiceWorkgroup('0', $ContentJson, $CountMax);

            // insert receiver into frontend
            $LayoutRowAPI = new LayoutRow(new LayoutColumn(ApiWorkGroup::receiverWorkgroup($PipelineServiceWorkgroup), 4));
            for($i = 1; $i <= $CountMax; $i++){
                $LayoutRowAPI->addColumn(new LayoutColumn(ApiWorkGroup::receiverWorkgroup('', $i), 4));
            }

            $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
                //                new LayoutColumn(new Title($TypeFrontend)),
                new LayoutColumn(ApiWorkGroup::receiverLoad(ApiWorkGroup::pipelineLoad(0, $CountMax))),
                new LayoutColumn('<div style="height: 15px"> </div>'),
                )),
                $LayoutRowAPI
            ))));
        } else {
            $Stage->setContent(
                new Warning(new Center('Es sind keine Transaktionen verfügbar.'))
            );
        }

        return $Stage;
    }

    /**
     * @param bool $isSekII
     *
     * @return false|TblDivisionCourse[]
     */
    private function getDivisionCourseList(bool $isSekII = false)
    {
        $DivisionCourseList = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                if(($tblDivisionCourseCoreGroupList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))){
                    $DivisionCourseList = array_merge($DivisionCourseList, $tblDivisionCourseCoreGroupList);
                }
                if($isSekII) {
                    if(($tblDivisionCourseBasic = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                        TblDivisionCourseType::TYPE_BASIC_COURSE))) {
                        $DivisionCourseList = array_merge($DivisionCourseList, $tblDivisionCourseBasic);
                    }
                    if(($tblDivisionCourseAdvanced = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                        TblDivisionCourseType::TYPE_ADVANCED_COURSE))) {
                        $DivisionCourseList = array_merge($DivisionCourseList, $tblDivisionCourseAdvanced);
                    }
                }
            }
        }
        return (!empty($DivisionCourseList) ? $DivisionCourseList : false);
    }

    /**
     * @param TblDivisionCourse $DivisionCourse
     *
     * @return string
     */
    public function isDivisionCourseValid($divisionName)
    {

        $error = '';
        if (!preg_match('!^[\w \-]+$!', $divisionName)) {
            $error = new DangerText(new ToolTip(new Remove(), 'Erlaubte Zeichen [a-zA-Z0-9 -]'));
        } else {
            if (preg_match('!^[ \-]!', $divisionName)) {
                $error = new DangerText(new ToolTip(new Remove(), 'Darf nicht mit einem "-" beginnen'));
            } elseif (preg_match('![ \-]$!', $divisionName)) {
                $error = new DangerText(new ToolTip(new Remove(), 'Darf nicht mit einem "-" aufhören'));
            }
        }
        return $error;
    }

    /**
     * @param Stage $Stage
     * @param array $List
     *
     * @return bool
     */
    private function errorScan(Stage $Stage, $List = array())
    {

        if(isset($List['detail'])){
            $Stage->setContent(new Warning('Fehlerbericht der API
                <pre>'.print_r($List, true).'</pre>'
            ));
            return true;
        }
        return false;
    }

    /**
     * @param array $Account
     * correct Account values return false
     * incorrect Accounts return ErrorLog
     *
     * @return array|bool
     */
    public function controlAccount($Account = array())
    {

        $ErrorLog = array();
        // Handle Error Entity
        // welche Eigenschaften müssen vorhanden sein:
        if($Account['name'] == ''
            || $Account['firstname'] == ''
            || $Account['lastname'] == ''
            || $Account['mail'] == ''
            || $Account['record_uid'] == ''
//            || $Account['recoveryMail'] == ''
            || empty($Account['school_classes'])
            || empty($Account['roles'])
            || empty($Account['schools'])
            || $Account['schoolCode'] == ''
        ) {

//            if($Account['name'] == 'REF-AlHa05'){
//                Debugger::devDump($Account);
//            }

            $tblMemberStudent = false;
            $tblPerson = false;

            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
            $tblGroupTeacher = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
            // ausnahme für Lehrer/Mitarbeiter ohne Klasse
            if(($tblAccount = Account::useService()->getAccountById($Account['record_uid']))){
                if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                    $tblPerson = current($tblPersonList);
                    $tblMemberStudent = Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup);
                }
            }
            if($tblPerson){
                $PersonId = $tblPerson->getId();
                $PersonLink = (new Link(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'),
                    '/People/Person', new Person(), array('Id' => $PersonId)))->setExternal();
            } else {
                $PersonLink = new Muted(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'));
            }
            $ErrorLog[] = new Bold($Account['name']).' '.$PersonLink;

            // Umlautkontrolle, wenn ein Nutzername vorhanden ist
            if($Account['name'] !== '' && Univention::useService()->checkName($Account['name'])){
                $ErrorLog[] = 'Benutzername: '.new DangerText('enthält Umlaute oder Sonderzeichen');
            }

            // Schularten, welche keine E-Mail als Benutzernamen benötigen
            $SchoolTypeList = Univention::useService()->getSchoolTypeException();

            foreach($Account as $Key => $Value){
                $MouseOver = '';
                $KeyReplace = '';
                if(is_array($Value)){
                    switch ($Key){
                        case 'roles':
                            $KeyReplace = 'Rolle:';
                            // sich ausschließende Gruppen vergeben, auch eine Fehlermeldung (roles wird im service geleert)
                            if($tblMemberStudent
                            && (Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStaff)
                              || Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupTeacher)
                                )){
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'Person mit sich ausschließenden Personengruppen:<br />'
                                    .new DangerText('Schüler, Mitarbeiter/Lehrer')
                                )))->enableHtml();
                            } else {
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'Person in keiner der folgenen Personengruppen:<br />'
                                    .new DangerText('Schüler, Mitarbeiter/Lehrer')
                                )))->enableHtml();
                            }
                        break;
                        case 'schools':
                            $KeyReplace = 'Schule:';
                            $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
                                'Schüler ist keiner Klasse zugewiesen <br />'
                                .'oder Schule fehlt in DLLP')))->enableHtml();
                        break;
                        case 'guardianList':
                            if(!empty($Value)){
                                foreach($Value as $UserName){
                                    if(!(UniventionTransfer::useService()->getUniventionAccountByName($UserName))){
                                        $KeyReplace = 'Sorgeberechtigte (liste):';
                                        $MouseOver = (new ToolTip($UserName. new InfoIcon(), htmlspecialchars(
                                            'Benutzer noch nicht in DLLP ')))->enableHtml();
                                    }
                                }
                            }
                        case 'legal_ward':
                            // darf/kann leer sein
                            break;
                        case 'udm_properties':
                            if(!$Value['schoolCode']){
                                $KeyReplace = 'DISCH:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
                                    'Dienststellenschlüssel nicht zugeordnet <br />'
                                    .'(Lehrauftrag / Schulverlauf / Mandant / Schule)')))->enableHtml();
                            }
                            break;

                    }
                    // Sonderregelung Schüler ohne Klasse ist ein Fehler Lehrer/Mitarbeiter nicht
                    if($tblMemberStudent && $Key == 'school_classes'){
                        $KeyReplace = 'Klassen:';
                        $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                            new DangerText('Fehler:').'<br />'.
                            'Schüler ist keiner Klasse zugewiesen')))->enableHtml();
                    } elseif(!$tblMemberStudent && $Key == 'school_classes') {
                        continue;
                    }
                    if(empty($Value)){
                        // Ausnahmen die keine Fehler leer keine Fehler erzeugen
                        if($Key != 'legal_guardians' && $Key != 'guardianList' && $Key != 'legal_wards'){
                            $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden!').$MouseOver;
                        }
                    }

                } else {
                    if($Value == ''){
                        switch ($Key){
                            case 'name':
                                $KeyReplace = 'Benutzername:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'</br>'.
                                    'Umlaute oder Sonderzeichen'
                                )))->enableHtml();
                            break;
                            case 'mail':
                                $KeyReplace = 'E-Mail:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'keine E-Mail als DLLP Benutzername verwendet'
                                )))->enableHtml();
                            break;
                            // recovery Mail optional
//                            case 'recoveryMail':
//                                $KeyReplace = 'E-Mail recovery:';
//                                $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
////                                    new DangerText('Fehler:').'<br />'.
//                                    'keine Passwort vergessen E-Mail hinterlegt'
//                                )))->enableHtml();
//                            break;
                            case 'lastname':
                                $KeyReplace = 'Person:';
                                $MouseOver = new ToolTip(new InfoIcon(), 'keine Person am Account');
                            break;
                            case 'school_classes':
                                $KeyReplace = 'Klasse:';
                                $MouseOver = new ToolTip(new InfoIcon(), 'Person muss mindestens einer Klasse zugewiesen sein');
                            break;
                            case 'schoolCode':
                                $KeyReplace = 'DISCH:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
                                    'Dienststellenschlüssel nicht zugeordnet <br />'
                                    .'(Lehrauftrag / Schulverlauf / Mandant / Schule)')))->enableHtml();
                            break;
                        }

                        if(empty($Value)){
                                // Mousover Problembeschreibung
                            switch($Key){
                                    // recoveryMail ist optional
                                case 'recoveryMail':
                                    // Schulart ist optional (Lehrer etc.)
                                case 'school_type':
                                    // role soll nicht einzeln kontrolliert werden
                                case 'role':
//                                    // Temporär deaktiviert
//                                case 'schoolCode':
                                // no log
                                break;

                                    //Mail wird für Schularten aus der Einstellung nicht geprüft
                                    // Accounts ohne Schulart sind von der Ausnahme nicht betroffen
                                case 'mail':
                                    if(!empty($SchoolTypeList) && in_array($Account['school_type'], $SchoolTypeList)){
                                        break;
                                    }
//                                    $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden! ').$MouseOver;
//                                    break;
                                default:
                                    $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden! ').$MouseOver;
                            }
                        }
                    }
                }
            }
        } elseif(Univention::useService()->checkName($Account['name'])){

            $tblPerson = false;
            if(($tblAccount = Account::useService()->getAccountById($Account['record_uid']))){
                if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                    $tblPerson = current($tblPersonList);
                }
            }
            if($tblPerson){
                $PersonLink = (new Link(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'),
                    '/People/Person', new Person(), array('Id' => $tblPerson->getId())))->setExternal();
            } else {
                $PersonLink = new Muted(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'));
            }

            $ErrorLog[] = new Bold($Account['name']).' '.$PersonLink;
            $ErrorLog[] = 'Benutzername: '.new DangerText('enthält Umlaute oder Sonderzeichen');
        } elseif(!empty($Account['guardianList'])) {

            $tblPerson = false;
            if(($tblAccount = Account::useService()->getAccountById($Account['record_uid']))){
                if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                    $tblPerson = current($tblPersonList);
                }
            }
            if($tblPerson){
                $PersonLink = (new Link(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'),
                    '/People/Person', new Person(), array('Id' => $tblPerson->getId())))->setExternal();
            } else {
                $PersonLink = new Muted(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'));
            }
            $ErrorLog[] = new Bold($Account['name']).' '.$PersonLink;

            if($Account['name'] == 'REF-AlFe30'){

            }

            foreach($Account['guardianList'] as $UserName){
                if(!(UniventionTransfer::useService()->getUniventionAccountByName($UserName))){
                    $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
                        'Benutzer noch nicht in DLLP ')))->enableHtml();
                    $ErrorLog[] = 'Sorgeberechtigter: '.new DangerText($UserName.' ').$MouseOver;
                }
            }


//            if($Account['name'] == 'REF-AlHa05'){
//                Debugger::devDump($Account);
//                Debugger::devDump($ErrorLog);
//            }
        }


        // Errorlog nur mit Namen wieder entfernen
        // Count 1 ist nur der Name ohne Fehlermeldung und ist im allgemeinen ein ungültiger "Fund"
        // tritt nur bei der Sonderregel "Lehrer/Mitarbeiter" ohne Klassen auf
        if(count($ErrorLog) == 1){
            $ErrorLog = array();
        }

        return (!empty($ErrorLog) ? $ErrorLog : false);
    }

    /**
     * @return Stage
     */
    public function frontendUnivCSV()
    {
        $Stage = new Stage('DLLP', 'Schnittstelle CSV');
        $Stage->addButton(new Standard('CSV Mandant herunterladen', '/Api/Reporting/Univention/SchoolList/Download', new Download()));
        $Stage->addButton(new Standard('CSV User herunterladen', '/Api/Reporting/Univention/User/Download', new Download(), array(), 'Beinhaltet alle Schüler/Mitarbeiter/Lehrer Accounts'));
        // Schularten, welche keine E-Mail als Benutzernamen benötigen
        $SchoolTypeList = Univention::useService()->getSchoolTypeException();

        $ErrorLog = array();
        $countFirstStudent = 0;
        if(($AccountPrepareList = Univention::useService()->getExportAccount(true))){
            $i = 0;
            foreach($AccountPrepareList as $Data){
                $IsError = false;
                if(!$Data['name']){
                    // nur Schüler können vorkommen, die keinen Account haben, der Rest wird nur über vorhandenen Account gezogen
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars('Person als '.
                            new Bold('Schüler').' besitzt keinen Account')))->enableHtml().
                        new DangerText('Account fehlt ');
                    $IsError = true;
                } elseif(Univention::useService()->checkName($Data['name'])) {
                    // Umlaute & Sonderzeichen im Benutzernamen sind nicht erlaubt
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars('Benutzername beinhaltet '.
                            new Bold('Umlaute oder Sonderzeichen'))))->enableHtml().
                        new DangerText('Account '.$Data['name']);
                    $Data['account'] = new DangerText('Umlaute&nbsp;oder&nbsp;Sonderzeichen');
                    $IsError = true;
                }
                if(!$Data['schools']){
                    $Data['schools'] = (new ToolTip(new Exclamation(),
                            htmlspecialchars(new Minus().' Lehrer erhält alle Schulen aus Mandanteneinstellungen<br/>'
                                .new Minus().' Schüler benötigt aktuelle Klasse<br/>'
                                .new Minus().' Schüler benötigt aktuelle Schule in S-Akte'
                            )))->enableHtml().
                        new DangerText(' Keine Schule hinterlegt');
                    $IsError = true;
                } else {
//                    $Data['schools'] = new SuccessText(new SuccessIcon().' gefunden');
                    $Data['schools'] = false;
                }

                if(!$Data['school_classes'] && preg_match("/student/",$Data['roles'])){

                    $Data['school_classes'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().
                            ' Schüler benötigt eine aktuelle Klasse')))->enableHtml().
                        new DangerText(' Keine Klasse');
                    $IsError = true;
                } else {
//                    $Data['school_classes'] = new SuccessText(new SuccessIcon().' gefunden');
                    $Data['school_classes'] = false;
                }

                //Mail wird für Schularten aus der Einstellung nicht geprüft
                $isExcluded = in_array($Data['school_type'], $SchoolTypeList);

                if(!$Data['mail'] && !$isExcluded){
                    $Data['mail'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().'
                    Keine E-mail als '.new Bold('DLLP Benutzername').' gepflegt')))->enableHtml().
                    new DangerText('kein DLLP Benutzername');
                    $IsError = true;
                } else {
                    $Data['mail'] = false;
//                    $Data['mail'] = new SuccessText(new SuccessIcon().' gefunden');
                }
                if($IsError){
                    $i++;
                    if($countFirstStudent == 0
                        && isset($Data['Type'])
                        && $Data['Type'] == 'Student'){
                        $countFirstStudent = $i;
                    }

                    $ErrorLog[] = $Data;
                }
            }
        }

        $countWarning = 0;
        $LayoutRowList = array();
        if(!empty($ErrorLog)){
            $LayoutRowCount = 0;
            $LayoutRow = null;
            foreach ($ErrorLog as $Notification){
                $countWarning++;
                $PanelContent = array();
                $PanelContent[] = 'Person: '.$Notification['firstname'].' '. $Notification['lastname'];
                if($Notification['schools']){
                    $PanelContent[] = 'Schule: '.$Notification['schools'];
                }
                if($Notification['school_classes']){
                    $PanelContent[] = 'Klasse: '.$Notification['school_classes'];
                }
                if($Notification['mail']){
                    $PanelContent[] = 'E-mail: '.$Notification['mail'];
                }
                if(isset($Notification['account']) && $Notification['account']){
                    $PanelContent[] = 'Benutzername: '.$Notification['account'];
                }

                if ($LayoutRowCount % 6 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                    // Mitarbeiterzeile, wenn der erste Fehler kein Schüler ist
                    if($countWarning == 1 && $countFirstStudent != 1){
                        $LayoutRow->addColumn(new LayoutColumn(new Title('Mitarbeiter')));
                    }
                }

                // erster Fehler der auf einen Schüler zeigt -> Überschrift + Umbruch (Zählung von vorn)
                if($countWarning == ($countFirstStudent)){
                    $LayoutRow->addColumn(new LayoutColumn(new Title('Schüler')));
                    $LayoutRowCount = 0;
                }

                $LayoutRow->addColumn(new LayoutColumn(new Panel($Notification['name'], $PanelContent)
                    , 2));
                $LayoutRowCount++;
            }
        }

        $Stage->setcontent(new Layout(array(new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Title($countWarning.' Warnungen', 'insgesamt')
                )
            )
        ), new LayoutGroup(
            $LayoutRowList
        )
        )));

        return $Stage;
    }
}