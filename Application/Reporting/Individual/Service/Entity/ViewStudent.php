<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    const TBL_COMMON_GENDER_NAME = 'TblCommonGender_Name';
    const TBL_SALUTATION_SALUTATION = 'TblSalutation_Salutation';
    const TBL_PERSON_TITLE = 'TblPerson_Title';
    const TBL_PERSON_FIRST_NAME = 'TblPerson_FirstName';
    const TBL_PERSON_SECOND_NAME = 'TblPerson_SecondName';
    const TBL_PERSON_LAST_NAME = 'TblPerson_LastName';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';
    const TBL_COMMON_BIRTHDATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTHDATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_CITY = 'TblCity_City';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_ADDRESS_COUNTRY = 'TblAddress_Country';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';
    const TBL_PHONE_NUMBER = 'TblPhone_Number';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';
    const TBL_STUDENT_LOCKER_KEY_NUMBER = 'TblStudentLocker_KeyNumber';
    const TBL_STUDENT_LOCKER_LOCKER_NUMBER = 'TblStudentLocker_LockerNumber';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const SIBLINGS_COUNT = 'Sibling_Count';

    // S1
    const TBL_SALUTATION_SALUTATION_S1 = 'TblSalutation_Salutation_S1';
    const TBL_PERSON_TITLE_S1 = 'TblPerson_Title_S1';
    const TBL_PERSON_FIRST_NAME_S1 = 'TblPerson_FirstName_S1';
    const TBL_PERSON_SECOND_NAME_S1 = 'TblPerson_SecondName_S1';
    const TBL_PERSON_LAST_NAME_S1 = 'TblPerson_LastName_S1';
    const TBL_ADDRESS_STREET_NAME_S1 = 'TblAddress_StreetName_S1';
    const TBL_ADDRESS_STREET_NUMBER_S1 = 'TblAddress_StreetNumber_S1';
    const TBL_CITY_CODE_S1 = 'TblCity_Code_S1';
    const TBL_CITY_CITY_S1 = 'TblCity_City_S1';
    const TBL_CITY_DISTRICT_S1 = 'TblCity_District_S1';
    const TBL_PHONE_NUMBER_S1 = 'TblPhone_Number_S1';
    const TBL_MAIL_ADDRESS_S1 = 'TblMail_Address_S1';

    // S2
    const TBL_SALUTATION_SALUTATION_S2 = 'TblSalutation_Salutation_S2';
    const TBL_PERSON_TITLE_S2 = 'TblPerson_Title_S2';
    const TBL_PERSON_FIRST_NAME_S2 = 'TblPerson_FirstName_S2';
    const TBL_PERSON_SECOND_NAME_S2 = 'TblPerson_SecondName_S2';
    const TBL_PERSON_LAST_NAME_S2 = 'TblPerson_LastName_S2';
    const TBL_ADDRESS_STREET_NAME_S2 = 'TblAddress_StreetName_S2';
    const TBL_ADDRESS_STREET_NUMBER_S2 = 'TblAddress_StreetNumber_S2';
    const TBL_CITY_CODE_S2 = 'TblCity_Code_S2';
    const TBL_CITY_CITY_S2 = 'TblCity_City_S2';
    const TBL_CITY_DISTRICT_S2 = 'TblCity_District_S2';
    const TBL_PHONE_NUMBER_S2 = 'TblPhone_Number_S2';
    const TBL_MAIL_ADDRESS_S2 = 'TblMail_Address_S2';

    // S3
    const TBL_SALUTATION_SALUTATION_S3 = 'TblSalutation_Salutation_S3';
    const TBL_PERSON_TITLE_S3 = 'TblPerson_Title_S3';
    const TBL_PERSON_FIRST_NAME_S3 = 'TblPerson_FirstName_S3';
    const TBL_PERSON_SECOND_NAME_S3 = 'TblPerson_SecondName_S3';
    const TBL_PERSON_LAST_NAME_S3 = 'TblPerson_LastName_S3';
    const TBL_ADDRESS_STREET_NAME_S3 = 'TblAddress_StreetName_S3';
    const TBL_ADDRESS_STREET_NUMBER_S3 = 'TblAddress_StreetNumber_S3';
    const TBL_CITY_CODE_S3 = 'TblCity_Code_S3';
    const TBL_CITY_CITY_S3 = 'TblCity_City_S3';
    const TBL_CITY_DISTRICT_S3 = 'TblCity_District_S3';
    const TBL_PHONE_NUMBER_S3 = 'TblPhone_Number_S3';
    const TBL_MAIL_ADDRESS_S3 = 'TblMail_Address_S3';

    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblCommonGender_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthday;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthplace;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Country;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_KeyNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
    /**
     * @Column(type="string")
     */
    protected $Sibling_Count;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S1;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S1;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S2;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S2;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S3;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S3;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_COMMON_GENDER_NAME, 'Geschlecht');
        $this->setNameDefinition(self::TBL_PERSON_TITLE, 'Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME, 'Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME, 'Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME, 'Nachname');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Mitarbeitsbereitschaft');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Mitarbeits Aktivitäten');
        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Personenbemerkung');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHDAY, 'Geburtstag');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHPLACE, 'Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Staatsangehörigkeit');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME, 'Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER, 'Str. Nr.');
        $this->setNameDefinition(self::TBL_CITY_CODE, 'PLZ');
        $this->setNameDefinition(self::TBL_CITY_CITY, 'Stadt');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT, 'Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTRY, 'Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION, 'Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER, 'Telefonnummer');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'E-Mail');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Versicherung');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_KEY_NUMBER, 'Schließfach Schlüsselnummer');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_NUMBER, 'Schließfachnummer');
        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Schülernummer');
        $this->setNameDefinition(self::SIBLINGS_COUNT, 'Anzahl Geschwister');
        // S1
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S1, 'Anrede_S1');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S1, 'Titel_S1');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S1, 'Vorname_S1');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S1, 'Zweiter Vorname_S1');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S1, 'Nachname_S1');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S1, 'Straße_S1');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S1, 'Straße_S1');
        $this->setNameDefinition(self::TBL_CITY_CODE_S1, 'PLZ_S1');
        $this->setNameDefinition(self::TBL_CITY_CITY_S1, 'Stadt_S1');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S1, 'Ortsteil_S1');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S1, 'Telefon_S1');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S1, 'E-Mail_S1');
        // S2
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S2, 'Anrede_S2');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S2, 'Titel_S2');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S2, 'Vorname_S2');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S2, 'Zweiter Vorname_S2');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S2, 'Nachname_S2');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S2, 'Straße_S2');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S2, 'Straße_S2');
        $this->setNameDefinition(self::TBL_CITY_CODE_S2, 'PLZ_S2');
        $this->setNameDefinition(self::TBL_CITY_CITY_S2, 'Stadt_S2');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S2, 'Ortsteil_S2');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S2, 'Telefon_S2');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S2, 'E-Mail_S2');
        // S3
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S3, 'Anrede_S3');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S3, 'Titel_S3');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S3, 'Vorname_S3');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S3, 'Zweiter Vorname_S3');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S3, 'Nachname_S3');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S3, 'Straße_S3');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S3, 'Straße_S3');
        $this->setNameDefinition(self::TBL_CITY_CODE_S3, 'PLZ_S3');
        $this->setNameDefinition(self::TBL_CITY_CITY_S3, 'Stadt_S3');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S3, 'Ortsteil_S3');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S3, 'Telefon_S3');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S3, 'E-Mail_S3');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }
}