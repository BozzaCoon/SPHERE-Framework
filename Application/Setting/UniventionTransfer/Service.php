<?php
namespace SPHERE\Application\Setting\UniventionTransfer;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Univention;
use SPHERE\Application\Setting\Univention\UniventionUser;
use SPHERE\Application\Setting\UniventionTransfer\Service\Data;
use SPHERE\Application\Setting\UniventionTransfer\Service\Entity\TblUniventionAccount;
use SPHERE\Application\Setting\UniventionTransfer\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\UniventionTransfer
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
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $UserName
     * @return false|string
     */
    public function getUserURL(string $UserName)
    {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $Server = $tblUnivention->getValue();
            return 'https://'.$Server.'/v1/users/'.$UserName;
        }
        return false;
    }

    /**
     * @param string $Role
     * @return false|string
     */
    public function getRoleURL(string $Role = TblUniventionAccount::VALUE_STUDENT)
    {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $Server = $tblUnivention->getValue();
            return 'https://'.$Server.'/v1/roles/'.$Role;
        }
        return false;
    }

    /**
     * @param $Role
     * @return false|string
     */
    public function getSchoolURL()
    {
        $Acronym = Account::useService()->getMandantAcronym();
        if($Acronym && ($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $Server = $tblUnivention->getValue();
            return 'https://'.$Server.'/v1/schools/'.$Acronym;
        }
        return false;
    }

    public function getUniventionAccountAll()
    {

        return (new Data($this->getBinding()))->getUniventionAccountAll();
    }

    /**
     * @param string $Role
     * @return false|TblUniventionAccount[]
     */
    public function getUniventionAccountListByRole(string $Role)
    {

        return (new Data($this->getBinding()))->getUniventionAccountListByRole($Role);
    }

    /**
     * @param string $Role
     * @return false|TblUniventionAccount[]
     */
    public function getUniventionAccountListByRoleLike(string $Role)
    {

        return (new Data($this->getBinding()))->getUniventionAccountListByRoleLike($Role);
    }

    public function getUniventionAccountListTeacherAndStuff()
    {

        $ResultList = array();
        if(($result = (new Data($this->getBinding()))->getUniventionAccountListByRoleLike(TblUniventionAccount::VALUE_TEACHER))){
            $ResultList = array_merge($ResultList, $result);
        }
        if(($result = (new Data($this->getBinding()))->getUniventionAccountListByRoleLike(TblUniventionAccount::VALUE_STAFF))){
            $ResultList = array_merge($ResultList, $result);
        }
        return $ResultList;
    }

    public function getUniventionAccount()
    {

        return (new Data($this->getBinding()))->getUniventionAccount();
    }

    /**
     * @param string $UserName
     * @return TblUniventionAccount
     */
    public function getUniventionAccountByName(string $UserName)
    {

        return (new Data($this->getBinding()))->getUniventionAccountByName($UserName);
    }

    public function convertToArray($tblUniventionTransferList)
    {

        $UserUniventionList = array();
        /* @var $tblUniventionAccount TblUniventionAccount */
        foreach($tblUniventionTransferList as $tblUniventionAccount){
            $UserUniventionList[$tblUniventionAccount->getRecordUid()] = array(
                'record_uid' => $tblUniventionAccount->getRecordUid(),
                'name' => $tblUniventionAccount->getName(),
                'school' => $tblUniventionAccount->getSchools(),
                'firstname' => $tblUniventionAccount->getFirstname(),
                'lastname' => $tblUniventionAccount->getLastname(),
                'mail' => $tblUniventionAccount->getMail(),
                'role' => $tblUniventionAccount->getRole(),
                'roles' => $tblUniventionAccount->getRoleUrl(),
                'schools' => $tblUniventionAccount->getSchools(),
                'school_classes' => $tblUniventionAccount->getSchoolClasses(),

//                'e-mail' => array($tblUniventionAccount->getMail()),
                'recoveryMail' => $tblUniventionAccount->getMailRecovery(),
//                'DllpServiceAccount' => $tblUniventionAccount->getDllpServiceAccount(),
                'schoolCode' => $tblUniventionAccount->getDllpDISCH(),
                'guardians' => $tblUniventionAccount->getGuardians(),
                'guardianList' => $tblUniventionAccount->getGuardianAccountNameList(),
                'wards' => $tblUniventionAccount->getWards(),
                'wardList' => $tblUniventionAccount->getWardAccountNameList(),

//                'udm_properties' => array(
//                    'e-mail' => array($tblUniventionAccount->getMail()),
//                    'PasswordRecoveryEmail' => $tblUniventionAccount->getMailRecovery(),
//                    'DllpServiceAccount' => $tblUniventionAccount->getDllpServiceAccount(),
//                    'DllpDienststellenschluessel' => $tblUniventionAccount->getDllpDISCH(),
//                ),
            );
        }

        return $UserUniventionList;
    }

    public function createIndiwareStudentSubjectOrderBulk()
    {

        $Acronym = Account::useService()->getMandantAcronym();
//        $Acronym = 'CSW';
        $ImportList = array();
        if(($UniventionUserList = (new UniventionUser())->getUserListByProperty('name',$Acronym.'-', true))){
            foreach($UniventionUserList as $UniventionUser){
                // Teacher && Staff vor Guardian
                rsort($UniventionUser['roles']);
                sort($UniventionUser['legal_guardians']);
                sort($UniventionUser['legal_wards']);
                $item = array();
                $item['Dn'] = $UniventionUser['dn'];
                $item['Name'] = $UniventionUser['name'];
                $item['Record_uid'] = $UniventionUser['record_uid'];
                $item['Firstname'] = $UniventionUser['firstname'];
                $item['Lastname'] = $UniventionUser['lastname'];
//                $item['Birthday'] = $UniventionUser['birthday'];
                $item['Role'] = null;
                $item['RoleUrl'] = $UniventionUser['roles'];
                $item['School_classes'] = $UniventionUser['school_classes'];
                $item['Schools'] = $UniventionUser['schools'];
                $item['Workgroups'] = null;
                $item['GuardianList'] = $UniventionUser['legal_guardians'];
                $item['WardList'] = $UniventionUser['legal_wards'];
//                $item['Url'] = $UniventionUser['url'];
                $item['Mail'] = null;
                $item['MailRecovery'] = null;
                $item['DllpServiceAccount'] = false;
                $item['DllpDISCH'] = null;

                if(($WorkgroupList = $UniventionUser['workgroups'])){
                    $GroupList = array();
                    foreach($WorkgroupList as $School => $GroupList){
                        foreach($GroupList as $Group){
                            $GroupList[] = $School.'-'.$Group;
                        }
                    }
                    $item['Workgroups'] = implode(';', $GroupList);
                }
                if(($value = $UniventionUser['roles'])){
                    if(is_array($value)){
                        foreach($value as &$role){
                            $role = baseName($role);
                        }
                        $item['Role'] = implode(', ', $value); // lastPart
                    }
                }
                if($UniventionUser['udm_properties']['e-mail'] != ''){
                    $item['Mail'] = current($UniventionUser['udm_properties']['e-mail']);
                }
                if($UniventionUser['udm_properties']['PasswordRecoveryEmail'] != ''){
                    $item['MailRecovery'] = $UniventionUser['udm_properties']['PasswordRecoveryEmail'];
                }
                if($UniventionUser['udm_properties']['DllpServiceAccount'] == 'true'){
                    $item['DllpServiceAccount'] = true;
                }
                if(($value = $UniventionUser['udm_properties']['DllpDienststellenschluessel'])){
                    $item['DllpDISCH'] = $value;
                }

                array_push($ImportList, $item);
            }
        }
        (new Data($this->getBinding()))->destroyUniventionAccountAllBulk();
        (new Data($this->getBinding()))->createUniventionAccountBulk($ImportList);
    }

}