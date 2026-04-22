<?php
namespace SPHERE\Application\Transfer\SaxSVS\Export;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Transfer\SaxSVS\Export\Service\Data;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\Application\Transfer\SaxSVS\Export\Service\Setup;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\SaxSVS\Export
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

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    public function getCompleteJsonByCompanyAndType($tblCompany, $tblSchoolType)
    {

        $tblSchoolList = School::useService()->getSchoolAll();
        $tblSchool = $tblSchoolList[0];
        $tblCompany = $tblSchool->getServiceTblCompany();
        $tblSchoolType = $tblSchool->getServiceTblType();
        $StudentEducationList = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                $StudentEducationTempList = DivisionCourse::useService()->getStudentEducationListByYearAndInstitutionAndType($tblYear, $tblCompany, $tblSchoolType);
                $StudentEducationList = array_merge($StudentEducationList, $StudentEducationTempList);
            }
        }
        $TableContent = array();

        if(!empty($StudentEducationList)){
            $ArrayLive = array();
            $tblStudentEducationOne = $StudentEducationList[0];
            $this->getStudentStaticArray($tblStudentEducationOne, $ArrayLive);
            $StudentList = array();
            $DivisionList = array();
            /**
             * @var TblStudentEducation $StudentEducation
             */
            foreach($StudentEducationList as $StudentEducation){
                $StudentList[] = $this->getStudentArray($StudentEducation);
                $this->getDivisionArray($StudentEducation, $DivisionList);
            }
            $result = array(
                'metadata' => $ArrayLive,
                'classes' => $DivisionList,
                'students' => $StudentList,
            );

            //ToDO erstmal result ausgegeben
            return $result;
        }

//        dump($StudentList);
//        $result = json_encode($result);
//        dump($result);

        return $TableContent;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param array               $ArrayLive
     *
     * @return void
     */
    private function getStudentStaticArray(TblStudentEducation $tblStudentEducation, array &$ArrayLive)
    {
        // Pflichtfelder:
        $ArrayLive['dsnr'] = '';
        $ArrayLive['schoolName'] = '';
        if(($tblCompany = $tblStudentEducation->getServiceTblCompany())){
            // Schultyp erst für dsnr wichtig
            if(($tblSchoolList = School::useService()->getSchoolByCompany($tblCompany))
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                foreach($tblSchoolList as $tblSchool){
                    if($tblSchool->getServiceTblType()->getId() == $tblSchoolType->getId()){
                        $ArrayLive['dsnr'] = $tblSchool->getSchoolCode();
                    }
                }
            }
            $ArrayLive['schoolName'] = $tblCompany->getName();
        }
        $ArrayLive['schoolYear'] = '';
        $tblYear = false;
        if(($tblYearList = Term::useService()->getYearByNow())){
            $tblYear = current($tblYearList);
        }
        if($tblYear){
            $ArrayLive['schoolYear'] = $tblYear->getYearFullString();
        }
    }

    public function getStudentArray(TblStudentEducation $tblStudentEducation)
    {

        $tblPerson = $tblStudentEducation->getServiceTblPerson();
        $tblDivision = $tblStudentEducation->getTblDivision();
        $tblCoreGroup = $tblStudentEducation->getTblCoreGroup();

        $student = array();
        $student['uuid'] = $tblPerson->getUuid();
        $student['name'] = $tblPerson->getLastName();
        $student['firstName'] = $tblPerson->getFirstName();

        if(($tblCommonGender = $tblPerson->getGender())){
            $student['gender'] = $tblCommonGender->getName();
            $student['genderSaxId'] = $this->getGenderSaxString($tblPerson->getGender());
        }
        $student['dateOfBirth'] = $tblPerson->getBirthday('Y-m-d');

        // class is optional, wenn nicht vorhanden, wird es nicht mitgeschickt
        if($tblDivision){
            $student['class'] = $tblDivision->getUuid();
        }elseif($tblCoreGroup){
            $student['class'] = $tblCoreGroup->getUuid();
        }


        $student['address'] = array();
        if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
            if(($tblCountry = $tblAddress->getTblCountry())){
                $student['address']['country'] = $tblCountry->getExtern();
            }
            if($tblCity = $tblAddress->getTblCity()){
                $student['address']['postalCode'] = $tblCity->getCode();
                $student['address']['city'] = $tblCity->getName();
            }
            $student['address']['street'] = $tblAddress->getStreetName();
            $student['address']['houseNumber'] = $tblAddress->getStreetNumber();
        }

        $student['contacts'] = array();
        $tblToPersonList = array();
        if(($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))
        && ($tblToPersonTempList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
            $tblToPersonList = array_merge($tblToPersonList, $tblToPersonTempList);
        }
        if(($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_AUTHORIZED))
        && ($tblToPersonTempList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
            $tblToPersonList = array_merge($tblToPersonList, $tblToPersonTempList);
        }
        if(($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN_SHIP))
        && ($tblToPersonTempList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
            $tblToPersonList = array_merge($tblToPersonList, $tblToPersonTempList);
        }
        if(!empty($tblToPersonList)){
            $CustodyArray = array();
            foreach($tblToPersonList as $tblToPerson){
                $tblPersonFrom = $tblToPerson->getServiceTblPersonFrom();
                $CustodyArray[] = array(
                    "name" => $tblPersonFrom->getLastName(),
                    "firstName" => $tblPersonFrom->getFirstName(),
                );
            }
            $student['contacts'] = $CustodyArray;
        }

        return $student;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param array               $DivisionList
     *
     * @return void
     */
    private function getDivisionArray(TblStudentEducation $tblStudentEducation, array &$DivisionList): void
    {

        if(($tblDivision = $tblStudentEducation->getTblDivision())){
            $DivisionList[$tblDivision->getId()]['uuid'] = $tblDivision->getUuid();
            $DivisionList[$tblDivision->getId()]['grade'] = $tblStudentEducation->getLevel(); //ToDO Verknüpfungstabelleneintrag
            $DivisionList[$tblDivision->getId()]['name'] = $tblDivision->getName();
        }
        if(($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())){
            $DivisionList[$tblCoreGroup->getId()]['uuid'] = $tblCoreGroup->getUuid();
            $DivisionList[$tblCoreGroup->getId()]['grade'] = $tblStudentEducation->getLevel(); //ToDO Verknüpfungstabelleneintrag
            $DivisionList[$tblCoreGroup->getId()]['name'] = $tblCoreGroup->getName();
        }
    }

    /**
     * @param bool|TblCommonGender $tblCommonGender
     *
     * @return string
     */
    public function getGenderSaxString(bool|TblCommonGender $tblCommonGender = false)
    {

        $externId = '';
        if(!$tblCommonGender){
            return $externId;
        }
        switch ($tblCommonGender->getName()) {
            case 'Männlich':
                $externId = "1";
                break;
            case 'Weiblich':
                $externId = "2";
                break;
            case 'Divers':
                $externId = "3";
                break;
            case 'Ohne Angabe':
                $externId = "4";
                break;
        }
        return $externId;
    }
}