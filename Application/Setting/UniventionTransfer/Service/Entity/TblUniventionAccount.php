<?php
namespace SPHERE\Application\Setting\UniventionTransfer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * @Entity
 * @Table(name="tblUniventionAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblUniventionAccount extends Element
{

//    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATTR_NAME = 'Name';
    const ATTR_ROLE = 'Role';
    const ATTR_DLLP_SERVICE_ACCOUNT = 'DllpServiceAccount';
    const VALUE_ALL = 'all';
    const VALUE_STUDENT = 'student';
    const VALUE_STAFF = 'staff';
    const VALUE_TEACHER = 'teacher';
    const VALUE_GUARDIAN = 'legal_guardian';
    const VALUE_WARD = 'legal_ward';

    /** @Column(type="string") */
    protected $Dn;
    /** @Column(type="string") */
    protected $Name;
    /** @Column(type="string") */
    protected $Firstname;
    /** @Column(type="string") */
    protected $Lastname;
    /** @Column(type="string") */
    protected $Record_uid;
    /** @Column(type="string") */
    protected $Role;
    /** @Column(type="string") */
    protected $RoleUrl;         // JSON
    /** @Column(type="string") */
    protected $School_classes;  // JSON
    /** @Column(type="string") */
    protected $Schools;          // JSON
    /** @Column(type="string") */
    protected $Workgroups;
    //ToDO Umbenennen
    /** @Column(type="string") */
    protected $Guardians;
    /** @Column(type="string") */
    protected $Wards;
//    /** @Column(type="string") */
//    protected $Url;
    /** @Column(type="string") */
    protected $Mail;
    /** @Column(type="string") */
    protected $MailRecovery;
    /** @Column(type="boolean") */
    protected $DllpServiceAccount;
    /** @Column(type="string") */
    protected $DllpDISCH;

    /**
     * @return string
     */
    public function getDn()
    {
        return $this->Dn;
    }

    /**
     * @param string $Dn
     */
    public function setDn($Dn): void
    {
        $this->Dn = $Dn;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->Firstname;
    }

    /**
     * @param string $Firstname
     */
    public function setFirstname($Firstname): void
    {
        $this->Firstname = $Firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->Lastname;
    }

    /**
     * @param string $Lastname
     */
    public function setLastname($Lastname): void
    {
        $this->Lastname = $Lastname;
    }

    /**
     * @return string
     */
    public function getRecordUid()
    {
        return $this->Record_uid;
    }

    /**
     * @param string $Record_uid
     */
    public function setRecordUid($Record_uid): void
    {
        $this->Record_uid = $Record_uid;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->Role;
    }

    /**
     * @param string $Role
     */
    public function setRole($Role): void
    {
        $this->Role = $Role;
    }

    /**
     * @return string
     */
    public function getRoleUrl()
    {
        // JSON in Array umwandeln
        return json_decode($this->RoleUrl, true);
    }

    /**
     * @param array $RoleUrl
     */
    public function setRoleUrl($RoleUrl): void
    {
        // Array in JSON umwandeln
        $this->RoleUrl = json_encode($RoleUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getSchoolClasses()
    {
        // JSON in Array umwandeln
        return json_decode($this->School_classes, true);
    }

    /**
     * @param string $School_classes
     */
    public function setSchoolClasses($School_classes): void
    {
        // Array in JSON umwandeln
        $this->School_classes = json_encode($School_classes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getSchools()
    {
        // JSON in Array umwandeln
        return json_decode($this->Schools, true);
    }

    /**
     * @param string $Schools
     */
    public function setSchools($Schools): void
    {
        // Array in JSON umwandeln
        $this->Schools = json_encode($Schools, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getWorkgroups()
    {
        // JSON in Array umwandeln
        return json_decode($this->Workgroups, true);
    }

    /**
     * @param string $Workgroups
     */
    public function setWorkgroups($Workgroups): void
    {
        // Array in JSON umwandeln
        $this->Workgroups = json_encode($Workgroups, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getGuardians()
    {
        // JSON in Array umwandeln
        return json_decode($this->Guardians, true);
    }

    /**
     * @return string
     */
    public function getGuardianAccountNameList()
    {
        // JSON in Array umwandeln
        $GuardianList = json_decode($this->Guardians, true);
        if($GuardianList && !empty($GuardianList)){
            foreach($GuardianList as &$Guardian){
                // URL von der Kelvin REST API
                $Guardian = basename($Guardian); // lastPart

                // dn von der Kelvin REST API
//                if (preg_match('/^uid=([^,]+)/', $Guardian, $matches)) {
//                    $Guardian = $matches[1];
//                }
            }
        }

        return $GuardianList;
    }

    /**
     * @param string $Guardians
     */
    public function setGuardians($Guardians): void
    {
        // Array in JSON umwandeln
        $this->Guardians = json_encode($Guardians, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getWards()
    {
        // JSON in Array umwandeln
        return json_decode($this->Wards, true);
    }

    /**
     * @param string $Wards
     */
    public function setWards($Wards): void
    {
        // Array in JSON umwandeln
        $this->Wards = json_encode($Wards, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

//    /**
//     * @return string
//     */
//    public function getUrl()
//    {
//        return $this->Url;
//    }
//
//    /**
//     * @param string $Url
//     */
//    public function setUrl($Url): void
//    {
//        $this->Url = $Url;
//    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->Mail;
    }

    /**
     * @param string $Mail
     */
    public function setMail($Mail): void
    {
        $this->Mail = $Mail;
    }

    /**
     * @return string
     */
    public function getMailRecovery()
    {
        return $this->MailRecovery;
    }

    /**
     * @param string $MailRecovery
     */
    public function setMailRecovery($MailRecovery): void
    {
        $this->MailRecovery = $MailRecovery;
    }

    /**
     * @return bool
     */
    public function getDllpServiceAccount()
    {
        return $this->DllpServiceAccount;
    }

    /**
     * @param bool $DllpServiceAccount
     */
    public function setDllpServiceAccount($DllpServiceAccount): void
    {
        $this->DllpServiceAccount = $DllpServiceAccount;
    }

    /**
     * @return string
     */
    public function getDllpDISCH()
    {
        return $this->DllpDISCH;
    }

    /**
     * @param string $DllpDISCH
     */
    public function setDllpDISCH($DllpDISCH): void
    {
        $this->DllpDISCH = $DllpDISCH;
    }
}
