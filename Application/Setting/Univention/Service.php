<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Univention\Service\Data;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Service\Setup;
use SPHERE\Application\Setting\UniventionTransfer\Service\Entity\TblUniventionAccount;
use SPHERE\Application\Setting\UniventionTransfer\UniventionTransfer;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\TextBackground;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Univention
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol = '';
        if (!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData){
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $Type
     *
     * @return false|TblUnivention
     */
    public function getUnivention($Type)
    {

        return (new Data($this->getBinding()))->getUniventionByType($Type);
    }

    /**
     * @param string $Type
     * @param string $Value
     *
     * @return TblUnivention
     */
    public function createUnivention($Type, $Value)
    {

        return (new Data($this->getBinding()))->createUnivention($Type, $Value);
    }

    /**
     * @return array|bool|TblAccount
     */
    public function getAccountAllForAPITransfer($Type = 'all')
    {
        $tblAccountList = array();
        if($Type == 'all'){
            // Alle Mitarbeiter/Lehrer Account's
            if(($tblAccountListTemp = Account::useService()->getAccountAllForEdit())){
                $tblAccountList = $tblAccountListTemp;
            }

            // Student/Custody
            if ($tblUserAccountList = AccountUser::useService()->getUserAccountAll()){
                foreach ($tblUserAccountList as $tblUserAccount) {
                    if (($tblAccount = $tblUserAccount->getServiceTblAccount())){
                        $tblAccountList[] = $tblAccount;
                    }
                }
            }
            $tblAccountList = array_unique($tblAccountList);

        }

        if($Type == TblUniventionAccount::VALUE_TEACHER || $Type == TblUniventionAccount::VALUE_STAFF){
            // Alle Mitarbeiter/Lehrer Account's
            if(($tblAccountListTemp = Account::useService()->getAccountAllForEdit())){
                $tblAccountList = $tblAccountListTemp;
            }
        }

        if($Type == TblUniventionAccount::VALUE_STUDENT){
            if ($tblUserAccountList = AccountUser::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT)){
                foreach ($tblUserAccountList as $tblUserAccount) {
                    if ($tblUserAccount->getServiceTblAccount()){
                        $tblAccountList[] = $tblUserAccount->getServiceTblAccount();
                    }
                }
            }
        }

        if($Type == TblUniventionAccount::VALUE_GUARDIAN){
            // Lehrer mit Sorgerecht hinzufügen
            // Alle Mitarbeiter/Lehrer Account's
            if(($tblAccountListTemp = Account::useService()->getAccountAllForEdit())){
                foreach($tblAccountListTemp  as $tblAccountTemp){
                    if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccountTemp))){
                        // es existiert nur eine Person pro Account
                        if(($tblPerson = current($tblPersonList))){
                            $tblType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                            if(Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType)){
                                $tblAccountList[] = $tblAccountTemp;
                                continue;
                            }
                            $tblType = Relationship::useService()->getTypeByName('Vormund');
                            if(Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType)){
                                $tblAccountList[] = $tblAccountTemp;
                                continue;
                            }
                            $tblType = Relationship::useService()->getTypeByName('Bevollmächtigt');
                            if(Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType)){
                                $tblAccountList[] = $tblAccountTemp;
//                                continue;
                            }

                        }
                    }
                }
            }

            if ($tblUserAccountList = AccountUser::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY)){
                foreach ($tblUserAccountList as $tblUserAccount) {
                    if ($tblUserAccount->getServiceTblAccount()){
                        $tblAccountList[] = $tblUserAccount->getServiceTblAccount();
                    }
                }
            }
            $tblAccountList = array_unique($tblAccountList);
        }

        usort($tblAccountList, function ($a, $b) {
            return strcmp($a->getUserName(), $b->getUserName());
        });

        return (!empty($tblAccountList) ? $tblAccountList : false);
    }

    /**
     * @param TblYear[] $tblYear
     * @param string  $Type
     * @param string  $Acronym
     * @param array   $TeacherClasses
     * @param array   $ClassSchoolCodeList
     *
     * @return array
     */
    public function getAccountActive(
        $tblYearList,
        $Type,
        $Acronym = '',
        $TeacherClasses = array(),
        $ClassSchoolCodeList = array()
    ) {

        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer($Type);
        $activeAccountList = array();

        if($tblAccountList){
            array_walk($tblAccountList, function(TblAccount $tblAccount) use (
                $tblYearList,
                $Acronym,
                &$activeAccountList,
                $TeacherClasses,
                $ClassSchoolCodeList,
                $Type
            ) {
                // Reihenfolge für Fehleranzeige wichtig
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['role'] = '';
                $UploadItem['roles'] = array();
                $UploadItem['school_classes'] = array();
                $UploadItem['mail'] = $tblAccount->getUserAlias();
//                $UploadItem['password'] = $tblAccount->getPassword();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['schools'] = array(UniventionTransfer::useService()->getSchoolURL());
                $UploadItem['recoveryMail'] = $tblAccount->getRecoveryMail();
//            $UploadItem['password'] = '';// no passwort transfer
                $UploadItem['school_type'] = '';
                // Dienststellenschlüssel
                $UploadItem['schoolCode'] = '';
                // Guardian
                $UploadItem['legal_guardians'] = array();
                $UploadItem['guardianList'] = array();
                $UploadItem['legal_wards'] = array();
                $UploadItem['wardList'] = array();

                $tblDivisionCourse = false;
                $tblSchoolType = false;
                $tblCompany = false;
                $tblPerson = Account::useService()->getPersonAllByAccount($tblAccount);
                if($tblPerson){
                    $tblPerson = current($tblPerson);
                    $UploadItem['firstname'] = $tblPerson->getFirstName();
                    $UploadItem['lastname'] = $tblPerson->getLastName();
                    $tblDivisionCourse = false;
                    if($tblYearList){
                        foreach($tblYearList as $tblYear){
                            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                                $tblDivisionCourse = $tblStudentEducation->getTblDivision();
                                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                                if($tblDivisionCourse){
                                    break;
                                }
                            }
                        }
                    }
                    if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
                        foreach ($tblToPersonList as $tblToPerson) {
                            if(($tblType = $tblToPerson->getTblType())){
                                $TypeName = strtolower($tblType->getName());
                                if($Type == TblUniventionAccount::VALUE_STUDENT){
                                    // Sorgeberechtigte
                                    // legal_wards können nur Sorgeberechtigte erhalten sonst API Fehler (Lehrer erhalten Kinder also nur über den Sorgeberechtigten import)
                                    if($TypeName == 'sorgeberechtigt' || $TypeName == 'vormund' || $TypeName == 'bevollmächtigt'){
                                        if(($tblPersonCustody = $tblToPerson->getServiceTblPersonFrom())){
                                            // Sorgeberechtigte selber sollen ignoriert werden
                                            if($tblPerson->getId() !== $tblPersonCustody->getId()) {
                                                if(($tblAccountPersonList = Account::useService()->getAccountAllByPerson($tblPersonCustody))){
                                                    $tblAccountPerson = current($tblAccountPersonList);
                                                    $userName = $tblAccountPerson->getUsername();
                                                    // dn steht zwar in der Doku, ich erhalte aber eine User URL test diese zurück zu geben.
//                                                $UploadItem['legal_guardians'][] = 'uid='.$userName.',cn='.$TypeName.',cn=users,ou='.$Acronym.',dc=connexion,dc=evssn,dc=de';
                                                    // nur Sorgeberechtigte, wenn diese im DLLP vorhanden sind
                                                    if((UniventionTransfer::useService()->getUniventionAccountByName($userName))){
                                                        $UploadItem['legal_guardians'][] = UniventionTransfer::useService()->getUserURL($userName);
                                                        $UploadItem['guardianList'][] = $userName;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } elseif($Type == TblUniventionAccount::VALUE_GUARDIAN){
                                    // Kinder
                                    // legal_guardians können nur Schüler erhalten sonst API Fehler
                                    if($TypeName == 'sorgeberechtigt' || $TypeName == 'vormund' || $TypeName == 'bevollmächtigt'){
                                        if(($tblPersonStudent = $tblToPerson->getServiceTblPersonTo())){
                                            // Sorgeberechtigte selber sollen ignoriert werden
                                            if($tblPerson->getId() !== $tblPersonStudent->getId()) {
                                                if(($tblAccountPersonList = Account::useService()->getAccountAllByPerson($tblPersonStudent))){
                                                    $tblAccountPerson = current($tblAccountPersonList);
                                                    $userName = $tblAccountPerson->getUsername();
                                                    // dn steht zwar in der Doku, ich erhalte aber eine User URL test diese zurück zu geben.
//                                                $UploadItem['legal_guardians'][] = 'uid='.$userName.',cn='.$TypeName.',cn=users,ou='.$Acronym.',dc=connexion,dc=evssn,dc=de';
                                                    // nur Sorgeberechtigte, wenn diese im DLLP vorhanden sind
                                                    if((UniventionTransfer::useService()->getUniventionAccountByName($userName))){
                                                        $UploadItem['legal_wards'][] = UniventionTransfer::useService()->getUserURL($userName);
                                                        $UploadItem['wardList'][] = $userName;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if(!empty($UniventionUser['legal_guardians'])){
                            sort($UniventionUser['legal_guardians']);
                        }
                        if(!empty($UniventionUser['legal_wards'])){
                            sort($UniventionUser['legal_wards']);
                        }
                    }
                }
                // Klasse
                if($tblDivisionCourse){
                    $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                    $UploadItem['school_classes'][$Acronym][] = $ClassName;
                    if($tblSchoolType && ( $SchoolTypeString = $tblSchoolType->getShortName() ))
                        $UploadItem['school_type'] = $SchoolTypeString;
                } else {
                    if(isset($TeacherClasses[$tblPerson->getId()])){
                        $SchoolListWithClasses = $TeacherClasses[$tblPerson->getId()];
                        asort($SchoolListWithClasses);
                        $UploadItem['school_classes'] = $SchoolListWithClasses;
                        // SchoolCode
                        foreach($SchoolListWithClasses as $currentAcronym){
                            foreach($currentAcronym as $DivisionName) {
                                if(isset($ClassSchoolCodeList[$DivisionName]) && !empty($ClassSchoolCodeList[$DivisionName])) {
                                    $UploadItem['schoolCode'] = current($ClassSchoolCodeList[$DivisionName]);
                                    break;
                                }
                            }
                        }
                    }
                }
                // Dienststellenschlüssel Schüler
                if($tblCompany && $tblSchoolType){
                    if(($tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompany, $tblSchoolType))){
                        $UploadItem['schoolCode'] = $tblSchool->getSchoolCode();
                    }
                }
                // fallback für fehlende Zuweisungen (Schüler / Mitarbeiter / Lehrer ohne Lehrauftrag etc.)
                $tblSchoolMandantList = School::useService()->getSchoolAll();
                if($UploadItem['schoolCode'] == '' && $tblSchoolMandantList){
                    foreach($tblSchoolMandantList as $tblSchoolMandant){
                        if(($SchoolCode = $tblSchoolMandant->getSchoolCode())){
                            $UploadItem['schoolCode'] = $SchoolCode;
                            break;
                        }
                    }
                }
                // Rollen
                $roles = array();
                $isTeacher = $isStaff = $isGuardian = $isStudent = false;


                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $isTeacher = true;
                }
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $isStaff = true;
                }
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $isStudent = true;
                }
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $isGuardian = true;
                }

                // Schüler und andere Gruppen schließen sich aus
                if(!($isStudent && ($isTeacher || $isStaff || $isGuardian))){
                    if($isTeacher){
                        $roles[] = UniventionTransfer::useService()->getRoleURL(TblUniventionAccount::VALUE_TEACHER);
                    } elseif($isStaff){
                        $roles[] = UniventionTransfer::useService()->getRoleURL(TblUniventionAccount::VALUE_STAFF);
                    }
                    if($isGuardian){
                        $roles[] = UniventionTransfer::useService()->getRoleURL(TblUniventionAccount::VALUE_GUARDIAN);
                    }
                    if($isStudent){
                        $roles[] = UniventionTransfer::useService()->getRoleURL(TblUniventionAccount::VALUE_STUDENT);
                    }
                }
                if(!empty($roles)){
                    $UploadItem['roles'] = $roles;
                    foreach($roles as &$role){
                        $role = baseName($role);
                    }
//                    rsort($roles);
                    $UploadItem['role'] = implode(", ", $roles);
                }

                array_push($activeAccountList, $UploadItem);
            });
        }

        return $activeAccountList;
    }

    /**
     * @return array
     */
    public function getApiUser()
    {

        // Benutzerliste suchen
        $Acronym = Account::useService()->getMandantAcronym();
        $UniventionUserList = (new UniventionUser())->getUserListByProperty('name',$Acronym.'-', true);
        $UserUniventionList = array();
        if($UniventionUserList){
            $EmptyCount = 0;
            foreach($UniventionUserList as $User){
                //  Ignore DllpServiceAccounts with value 1
                if(isset($User['udm_properties']['DllpServiceAccount']) && $User['udm_properties']['DllpServiceAccount'] == '1'){
                    continue;
                }
                if(is_string($User)){ // strpos($User, 'error when reading'
                    echo '<pre> Antwort der API:<br/>'.print_r($User, true).'</pre>';
                    continue;
                }

                // Nutzer ohne record_uid müssen in das Array mit eigenem Key aufgenommen werden
                if(!$User['record_uid']){
                    $User['record_uid'] = 'E'.$EmptyCount++;
                }
                    // dn, url, ucsschool_roles[], name, school, firstname, lastname, birthday, disabled, email, record_uid,
                    // roles, schools, school_classes, source_uid, udm_properties
                $UserUniventionList[$User['record_uid']] = array(
                    'record_uid' => (isset($User['record_uid']) ? $User['record_uid'] : ''),
                    'name' => (isset($User['name']) ? $User['name'] : ''),
                    'school' => (isset($User['school']) ? $User['school'] : ''),
                    'firstname' => (isset($User['firstname']) ? $User['firstname'] : ''),
                    'lastname' => (isset($User['lastname']) ? $User['lastname'] : ''),
//                    'birthday' => (isset($User['birthday']) ? $User['birthday'] : ''),
                    'email' => (isset($User['email']) ? $User['email'] : ''),
                    'roles' => (isset($User['roles']) ? $User['roles'] : array()),
                    'schools' => (isset($User['schools']) ? $User['schools'] : array()),
//                    // set no content so -> get no content
                    'school_classes' => (($User['school_classes']) ? $User['school_classes'] : array()),
                    // Wird nur beim Import mitgegeben, benötigen wir aber nicht
//                    'source_uid' => (isset($User['source_uid']) ? $User['source_uid'] : ''),
//                    // get no content
                    'udm_properties' => (isset($User['udm_properties']) ? $User['udm_properties'] : array()),
                    // Liefert Array zurück, das Feld werden wir nicht benötigen.
//                    'e-mail' => (isset($User['udm_properties']['e-mail']) ? $User['udm_properties']['e-mail'] : ''),
                );
            }
        }
        return $UserUniventionList;
    }

    /**
     * @param string $YearId
     * @param string $Type
     *
     * @return array
     */
    public function getSchulsoftwareUser($YearId = '', $Type = TblUniventionAccount::VALUE_ALL)
    {

        $Acronym = Account::useService()->getMandantAcronym();
        // Lehraufträge
        $TeacherClasses = array();
        $tblYearList = array();
        if($YearId && ($tblYear = Term::useService()->getYearById($YearId))) {
            $tblYearList[] = $tblYear;
        } elseif(($tblYearListTemp = Term::useService()->getYearByNow())) {
            $tblYearList = $tblYearListTemp;
        }

        // Lehraufträge nur für Lehrer raussuchen
        // Sorgeberechtigte müssen das auch Auswerten -> darunter sind auch Lehrer möglich
        if($Type == TblUniventionAccount::VALUE_TEACHER || $Type == TblUniventionAccount::VALUE_ALL || TblUniventionAccount::VALUE_GUARDIAN){
            if($tblYearList){
                foreach($tblYearList as $tblYear) {
                    $this->getTeacherClassesByYear($Acronym, $tblYear, $TeacherClasses);
                }
                // ArrayKey muss immer eine normale Zählung bei 0 beginnend ohne Lücken erhalten 0,1,2,3...
                // Key PersonId
                foreach($TeacherClasses as &$AcronymTemp) {
                    // Key Acronym
                    foreach($AcronymTemp as &$ClassList) {
                        sort($ClassList);
                    }
                }
            }
        }

        $ClassSchoolCodeList = array();
        // DISCH nur an Schülern oder Lehrern über Lehraufträge
        if($Type == TblUniventionAccount::VALUE_STUDENT || $Type == TblUniventionAccount::VALUE_TEACHER || $Type == TblUniventionAccount::VALUE_ALL) {
            // Klassen Schulschlüssel liste aus Schülern ziehen
            if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))
                && ($tblPersonList = $tblGroup->getPersonList())) {
                foreach ($tblPersonList as $tblPerson) {
                    if($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson)) {
                        // nur Klasse
                        $tblDivisionCourse = $tblStudentEducation->getTblDivision();
                        $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                        $tblCompany = $tblStudentEducation->getServiceTblCompany();
                        if($tblDivisionCourse && $tblSchoolType && $tblCompany) {
                            if(($tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompany, $tblSchoolType))) {
                                $ClassSchoolCodeList[$tblDivisionCourse->getName()][] = $tblSchool->getSchoolCode();
                            }
                        }
                    }
                }
            }
            if(!empty($ClassSchoolCodeList)) {
                foreach ($ClassSchoolCodeList as &$ClassCodeList) {
                    $ClassCodeList = array_unique($ClassCodeList);
                }
            }
        }

        return Univention::useService()->getAccountActive($tblYearList, $Type, $Acronym, $TeacherClasses, $ClassSchoolCodeList);
    }

    /**
     * @param IFormInterface $form
     * @param null|array $Year
     *
     * @return IFormInterface|string
     */
    public function transferUserApi(
        IFormInterface $form, $userIdentifier, $Upload, $YearId, $Role
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $userIdentifier) {
            return $form;
        }

        return new Success('Daten werden übertragen')
            . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS, array(
                'userIdentifier' => $userIdentifier,
                'Upload' => $Upload,
                'YearId' => $YearId,
                'Role' => $Role
            ));
    }

    public function getCompareUserList($UserSchulsoftwareList, $UserUniventionList)
    {
        // create: AccountActive welche nicht in der API vorhanden sind
        $createList = [];
        $cantCreateList = [];
        // update: Accounts welche vorhanden sind, aber unterschiedliche Werte aufweisen
        $updateList = [];
        $cantUpdateList = [];
        // delete: Accounts, die in der API vorhanden sind, aber nicht in der Schulsoftware
        $deleteList = [];

        // Vergleich
        if (!empty($UserSchulsoftwareList)) {
            foreach ($UserSchulsoftwareList as $AccountActive) {
                $hasError = Univention::useFrontend()->controlAccount($AccountActive);
                $existsInUnivention = isset($UserUniventionList[$AccountActive['record_uid']]);

                if (!$existsInUnivention) {
                    if($hasError){
                        $cantCreateList[] = $hasError;
                    } else {
                        $createList[] = $AccountActive;
                    }
                } else {
                    if($hasError){
                        $cantUpdateList[] = $hasError;
                    } else {
                        $updateList[] = $AccountActive;
                    }
                    unset($UserUniventionList[$AccountActive['record_uid']]);
                }
            }
        }

        // Übrig gebliebene Univention-Accounts -> delete
        if (!empty($UserUniventionList)) {
            $deleteList = $UserUniventionList;
        }

        return [$createList, $cantCreateList, $updateList, $cantUpdateList, $deleteList];
    }

    public function getOkAndUpdateList($updateList, $UserUniventionList, $keyToCompareList)
    {

        $OkList = array();
        if (!empty($updateList)) {
            if(empty($keyToCompareList)){
                $keyToCompareList = ['firstname', 'lastname', 'email', 'roles', 'school_classes', 'recoveryMail', 'schoolCode'];
            }


            foreach ($updateList as &$AccountActive) {
                $KelvinActive = $UserUniventionList[$AccountActive['record_uid']] ?? null;
                $isDifferent = false;

                if ($KelvinActive === null) {
                    // Kein Vergleich möglich, da Datensatz fehlt
                    $isDifferent = true;
                } else {
                    foreach ($keyToCompareList as $key => $value) {
                        // Prüfe, ob Key in beiden Arrays existiert
                        if (!array_key_exists($key, $AccountActive) || !array_key_exists($key, $KelvinActive)) {
                            $isDifferent = true;
                            break;
                        }

                        // Vergleiche Werte (auch verschachtelte Arrays wie "roles" oder "school_classes")
                        if (is_array($AccountActive[$key]) || is_array($KelvinActive[$key])) {
                            // Vergleich über sortierte Arrays, um Reihenfolge auszuschließen
                            $a = $AccountActive[$key];
                            $b = $KelvinActive[$key];
                            sort($a);
                            sort($b);
                            if ($a !== $b) {
                                $isDifferent = true;
                                break;
                            }
                        } elseif ($AccountActive[$key] !== $KelvinActive[$key]) {
                            $isDifferent = true;
                            break;
                        }
                    }
                }

                if (!$isDifferent) {
                    $OkList[] = $AccountActive;
                    $AccountActive = false;
//                    unset($AccountActive);
                }
            }
        }
        $updateList = array_filter($updateList);
        return array($OkList, $updateList);
    }

    public function fillCompareRow($CompareRow, $ExistUser, $AccountActive)
    {

        $CompareRow['User'] = $AccountActive['name'];
        $CompareRow['DLLP'] = $this->fillCompareColumn($CompareRow['DLLP'], $ExistUser);
        $CompareRow['SSW'] = $this->fillCompareColumn($CompareRow['SSW'], $AccountActive, $CompareRow['DLLP']);
//        $CompareRow['SSWCopy'] = $CompareRow['SSW'];
        return $CompareRow;
    }

    public function fillOkRow($CompareRow, $AccountActive)
    {

        $CompareRow['User'] = $AccountActive['name'];
        $CompareRow['SSW'] = $this->fillCompareColumn($CompareRow['SSW'], $AccountActive);
        return $CompareRow;
    }

    private function fillCompareColumn($ColumnRow = array(), $DataInsert = array(), $ColumnCompareRow = array())
    {

        $Acronym = Account::useService()->getMandantAcronym();
        foreach($ColumnRow as $Key => &$Value){
            if(is_array($DataInsert[$Key])){
                if(isset($DataInsert[$Key][$Acronym])){
                    $Value = implode(', ', $DataInsert[$Key][$Acronym]);
                    if(!empty($ColumnCompareRow)){
                        if($Value != $ColumnCompareRow[$Key]){
                            $Value = new TextBackground($Value);
                        }
                    }
                } else {
                    $Value = implode(', ', $DataInsert[$Key]);
                    if(!empty($ColumnCompareRow)){
                        if($Value != $ColumnCompareRow[$Key]){
                            $Value = new TextBackground($Value);
                        }
                    }
                }
            } else {
                $Value = $DataInsert[$Key];
                if(!empty($ColumnCompareRow)){
                    if($Value != $ColumnCompareRow[$Key]){
                        $Value = new TextBackground($Value);
                    }
                }
            }
        }
        return $ColumnRow;
    }

    /**
     * @param $Acronym
     * @param $tblYear
     * @param $TeacherClasses
     *
     * @return void
     */
    private function getTeacherClassesByYear($Acronym, $tblYear, &$TeacherClasses)
    {
        if(($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear))){
            foreach($tblTeacherLectureshipList as $tblTeacherLectureship){
                $tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson();
                $tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse();
                if($tblPersonTeacher && $tblDivisionCourse && $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION){
                    $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                    $TeacherClasses[$tblPersonTeacher->getId()][$Acronym][$tblDivisionCourse->getId()] = $ClassName;
                }
                //                // doppelte werte entfernen
                //                $TeacherClasses[$tblPersonTeacher->getId()][$Acronym] = array_unique($TeacherClasses[$tblPersonTeacher->getId()][$Acronym]);
            }
        }
    }

    /**
     * @param bool $StudentWithoutAccount
     *
     * @return false|array
     */
    public function getExportAccount($StudentWithoutAccount = true)
    {

        $Acronym = Account::useService()->getMandantAcronym();
        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();

        $UploadToAPI = array();
        $TeacherClasses = array();

        // Lehraufträge
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear) {
                if(($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear))){
                    foreach($tblTeacherLectureshipList as $tblTeacherLectureship){
                        $tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson();
                        $tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse();
                        $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                        if(($tblSubject = $tblTeacherLectureship->getServiceTblSubject())){
                            $TeacherClasses[$tblPersonTeacher->getId()][$tblDivisionCourse->getId()] = $tblSubject->getAcronym().'-'.$ClassName;
                        }
                    }
                }
            }
        }

        if($tblAccountList){
            /** @var TblAccount $tblAccount */
            foreach ($tblAccountList as $tblAccount) {
                $UploadItem = array();
                $UploadItem['Type'] = 'Teacher';
                if($tblAccount->getHasAuthentication(TblIdentification::NAME_USER_CREDENTIAL)){
                    $UploadItem['Type'] = 'Student';
                }
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['roles'] = '';
                $UploadItem['schools'] = array();
                $UploadItem['mail'] = '';
                $UploadItem['BackupMail'] = '';
                $UploadItem['group'] = '';

                $UploadItem['password'] = '';
//                $UploadItem['password'] = $tblAccount->getPassword(); // ??
                $UploadItem['school_classes'] = '';
                $UploadItem['school_type'] = '';

                if ($tblPerson = Account::useService()->getPersonAllByAccount($tblAccount)){
                    $tblPerson = current($tblPerson);
                    $UploadItem = $this->getPersonDataExcel($UploadItem, $tblPerson, $tblYear, $Acronym, $TeacherClasses);
                } else {
                    // Ohne Person kein sinnvoller Account
                    continue;
                }

                if($UploadItem){
                    array_push($UploadToAPI, $UploadItem);
                }
            }
        }
        if($StudentWithoutAccount){
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if($tblPersonList){
                foreach($tblPersonList as $tblPerson){
                    if(Account::useService()->getAccountAllByPerson($tblPerson)){
                        // ignore students with account
                        continue;
                    }
                    $Item['Type'] = 'Student';
                    $Item['name'] = '';
                    $Item['firstname'] = '';
                    $Item['lastname'] = '';
                    $Item['record_uid'] = '';
                    $Item['source_uid'] = $Acronym.'-';
                    $Item['roles'] = '';
                    $Item['schools'] = array();
                    $Item['password'] = '';
                    $Item['school_classes'] = '';
                    $Item['school_type'] = '';
                    $Item['mail'] = '';
                    $Item['BackupMail'] = '';
                    $Item['group'] = '';

                    $Item = $this->getPersonDataExcel($Item, $tblPerson, $tblYear, $Acronym, $TeacherClasses);

                    if($Item){
                        array_push($UploadToAPI, $Item);
                    }
                }
            }
        }

        return (!empty($UploadToAPI) ? $UploadToAPI : false);
    }

    /**
     * @return array ShortName of SchoolTypes as array
     */
    public function getSchoolTypeException(){
        $list = array();
        if(!($tblSetting = Consumer::useService()->getSetting('Setting', 'Univention', 'Univention', 'API_Mail'))){
            return $list;
        }
        $Value = $tblSetting->getValue();
        if($Value != '' && ($TypeList = explode(',', $Value))){
            foreach($TypeList as $Type){
                if(($tblType = Type::useService()->getTypeByShortName(trim($Type)))){
                    $list[] = $tblType->getShortName();
                }
            }
        }
        return $list;
    }

    /**
     * @param array     $Item
     * @param TblPerson $tblPerson
     * @param TblYear   $tblYear
     * @param string    $Acronym
     * @param array     $TeacherClasses
     *
     * @return bool|array
     */
    private function getPersonDataExcel(
        array $Item,
        TblPerson $tblPerson,
        TblYear $tblYear,
        $Acronym,
        $TeacherClasses
    ) {

        $Item['firstname'] = $tblPerson->getFirstName();
        $Item['lastname'] = $tblPerson->getLastName();

        // Rollen
        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
        $roles = array();
        if($tblGroupList && !empty($tblGroupList)){
            foreach ($tblGroupList as $tblGroup) {
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_STAFF){
                    $roles[] = 'staff';
                }
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_TEACHER){
                    $roles[] = 'teacher';
                }
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_STUDENT){
                    $roles[] = 'student';
                }
            }
        }
        if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
            if(($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                $Item['group'] = $tblDivisionCourseCoreGroup->getName();
            }
        }

        // decide teacher / Stuff
        if(in_array('staff', $roles) && in_array('teacher', $roles)){
            $roles = array('teacher');
        }

        if(empty($roles)){
            // Accounts die nicht/nicht mehr zu den 3 Rollen gehören sollen entfernt werden
            return false;
        }
        $Item['roles'] = implode(',', $roles);
        $Item['schools'] = $Acronym;
        // Student Search Division
        if(($StudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            if(($tblDivisionCourse = $StudentEducation->getTblDivision())
            && $tblSchoolType = $StudentEducation->getServiceTblSchoolType()){
                $Item['school_classes'] = $Acronym.'-'.$this->getCorrectionClassNameByDivision($tblDivisionCourse);
                $Item['school_type'] = $tblSchoolType->getShortName();
            }
        } else {
            if(isset($TeacherClasses[$tblPerson->getId()])) {
                $ClassList = $TeacherClasses[$tblPerson->getId()];
                sort($ClassList);
                $Item['school_classes'] = implode(',', $ClassList);
            }
        }

        if($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson)){
            $tblAccount = current($tblAccountList);
            $Item['mail'] = $tblAccount->getUserAlias();
            $Item['BackupMail'] = $tblAccount->getRecoveryMail();
        }
        return $Item;
    }

    /**
     * @return false|FilePointer
     */
    public function downlaodAccountExcel()
    {

        $AccountData = $this->getExportAccount(false);

        if (!empty($AccountData))
        {

            $fileLocation = Storage::createFilePointer('csv');

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "uid");
            $export->setValue($export->getCell($column++, $row), "Schulen_OU");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Rollen");
            $export->setValue($export->getCell($column++, $row), "Klassen");
            $export->setValue($export->getCell($column++, $row), "Benutzername");
            $export->setValue($export->getCell($column++, $row), "Passwort");
            $export->setValue($export->getCell($column++, $row), "Externe_Mailadresse");
            $export->setValue($export->getCell($column++, $row), "PW_vergessen_Mail");
            $export->setValue($export->getCell($column ,$row++), "Stammgruppe");
            foreach ($AccountData as $Account)
            {
                // Accounts mit Umlauten überspringen
                if($this->checkName($Account['name'])){
                    continue;
                }
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $Account['record_uid']);
                $export->setValue($export->getCell($column++, $row), $Account['schools']);
                $export->setValue($export->getCell($column++, $row), $Account['firstname']);
                $export->setValue($export->getCell($column++, $row), $Account['lastname']);
                $export->setValue($export->getCell($column++, $row), $Account['roles']);
                $export->setValue($export->getCell($column++, $row), $Account['school_classes']);
                $export->setValue($export->getCell($column++, $row), $Account['name']);
                $export->setValue($export->getCell($column++, $row), $Account['password']);
                $export->setValue($export->getCell($column++, $row), $Account['mail']);
                $export->setValue($export->getCell($column++, $row), $Account['BackupMail']);
                $export->setValue($export->getCell($column, $row++), $Account['group']);
            }

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return false|FilePointer
     */
    public function downlaodSchoolExcel()
    {

        $OU = '';
        $Schulname = '';
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                $OU = $tblConsumer->getAcronym();
                $Schulname = $tblConsumer->getName();
            }
        }

        if ($OU && $Schulname)
        {

            $fileLocation = Storage::createFilePointer('csv');

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "OU");
            $export->setValue($export->getCell($column, $row), "Schulname");
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $OU);
            $export->setValue($export->getCell($column, $row), $Schulname);

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return string
     */
    public function getCorrectionClassNameByDivision(TblDivisionCourse $tblDivisionCourse = null)
    {
        $ClassName = $tblDivisionCourse->getName();
        $ClassName = str_replace('ä', 'ae', $ClassName);
        $ClassName = str_replace('ü', 'ue', $ClassName);
        $ClassName = str_replace('ö', 'oe', $ClassName);
        $ClassName = str_replace('ß', 'ss', $ClassName);
        return $ClassName;
    }

    /**
     * return true if it's a problem with chars (Umlaute / Sonderzeichen)
     * @param $UserName
     *
     * @return bool
     */
    public function checkName($UserName)
    {
        if((preg_match('!(^[a-zA-Z0-9-]+)!', $UserName, $Match)) && strlen($Match[0]) != strlen($UserName)){
            // enthält andere Zeichen
            return true;
        }
        //alles ok
        return false;
    }
}