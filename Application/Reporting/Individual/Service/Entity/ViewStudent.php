<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';

    const SIBLINGS_COUNT = 'Sibling_Count';
    // Krankenakte
    const TBL_STUDENT_MEDICAL_RECORD_DISEASE = 'TblStudentMedicalRecord_Disease';
    const TBL_STUDENT_MEDICAL_RECORD_MEDICATION = 'TblStudentMedicalRecord_Medication';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE = 'TblStudentMedicalRecord_InsuranceState';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';
    // Taufe
    const TBL_STUDENT_BAPTISM_LOCATION = 'TblStudentBaptism_Location';
    // Schulbeförderung
    const TBL_STUDENT_TRANSPORT_ROUTE = 'TblStudentTransport_Route';
    const TBL_STUDENT_TRANSPORT_STATION_ENTRANCE = 'TblStudentTransport_StationEntrance';
    const TBL_STUDENT_TRANSPORT_STATION_EXIT = 'TblStudentTransport_StationExit';
    const TBL_STUDENT_TRANSPORT_REMARK = 'TblStudentTransport_Remark';
    // Unterrichtsbefreiung
    const TBL_STUDENT_LIBERATION_TYPE_NAME = 'TblStudentLiberationType_Name';
    const TBL_STUDENT_LIBERATION_TYPE_DESCRIPTION = 'TblStudentLiberationType_Description';
    const TBL_STUDENT_LIBERATION_CATEGORY_NAME = 'TblStudentLiberationCategory_Name';
    const TBL_STUDENT_LIBERATION_CATEGORY_DESCRIPTION = 'TblStudentLiberationCategory_Description';
    // Schließfach
    const TBL_STUDENT_LOCKER_LOCKER_NUMBER = 'TblStudentLocker_LockerNumber';
    const TBL_STUDENT_LOCKER_LOCKER_LOCATION = 'TblStudentLocker_LockerLocation';
    const TBL_STUDENT_LOCKER_KEY_NUMBER = 'TblStudentLocker_KeyNumber';

    /**
     * @return array
     */
    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentBaptism_Location;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerLocation;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_KeyNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Disease;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Medication;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_InsuranceState;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Route;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationEntrance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationExit;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Remark;
    /**
     * @Column(type="string")
     */
    protected $Sibling_Count;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
//        $this->setNameDefinition(self::TBL_STUDENT_BAPTISM_LOCATION, 'Schüler: Ort der Taufe');

        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_TYPE_NAME, 'Allgemeines: Unterrichtsbefreiung');
        // TBL_STUDENT_LIBERATION_TYPE_DESCRIPTION
        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_CATEGORY_NAME, 'Allgemeines: Unterrichtskategorie');
        // TBL_STUDENT_LIBERATION_CATEGORY_DESCRIPTION

        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_NUMBER, 'Allgemeines: Schließfachnummer');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_LOCATION, 'Allgemeines: Schließfach Standort');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_KEY_NUMBER, 'Allgemeines: Schließfach Schlüssel Nummer');

        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_DISEASE, 'Allgemeines: Krankheiten / Allergien');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION, 'Allgemeines: Medikamente');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE, 'Allgemeines: Versicherungsstatus');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Allgemeines: Krankenkasse');

        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_ROUTE, 'Allgemeines: Buslinie');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE, 'Allgemeines: Einstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_EXIT, 'Allgemeines: Ausstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_REMARK, 'Allgemeines: Bemerkung');

        $this->setNameDefinition(self::SIBLINGS_COUNT, 'Allgemeines: Anzahl Geschwister');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::SIBLINGS_COUNT,
            self::TBL_STUDENT_LIBERATION_TYPE_NAME,
            self::TBL_STUDENT_LIBERATION_CATEGORY_NAME,
            self::TBL_STUDENT_LOCKER_LOCKER_NUMBER,
            self::TBL_STUDENT_LOCKER_LOCKER_LOCATION,
            self::TBL_STUDENT_LOCKER_KEY_NUMBER,
            self::TBL_STUDENT_MEDICAL_RECORD_DISEASE,
            self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION,
            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE,
            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE,
            self::TBL_STUDENT_TRANSPORT_ROUTE,
            self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE,
            self::TBL_STUDENT_TRANSPORT_STATION_EXIT,
            self::TBL_STUDENT_TRANSPORT_REMARK
        ));
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

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::SIBLINGS_COUNT:
                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
                    $Placeholder, $Label, $Icon
                );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
