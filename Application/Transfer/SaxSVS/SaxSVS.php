<?php
namespace SPHERE\Application\Transfer\SaxSVS;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Transfer\SaxSVS\Export\Export;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class SaxSVS
 * @package SPHERE\Application\Transfer\SaxSVS
 */
class SaxSVS implements IApplicationInterface
{
    public static function registerApplication()
    {

        Export::registerModule();

//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('SaxSVS'))
//        );
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, __CLASS__.'::frontendDashboard'
//        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('SaxSVS', 'Datentransfer');

//        Debugger::devDump(Element::Uuid_v4());

        return $Stage;
    }

    private function getCompareTable()
    {

        $StudentEducationList = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                $StudentEducationTempList = DivisionCourse::useService()->getStudentEducationListBy($tblYear);
                $StudentEducationList = array_merge($StudentEducationList, $StudentEducationTempList);
            }
        }
        $TableContent = array();
        /**
         * @var TblStudentEducation $StudentEducation
         */
        foreach($StudentEducationList as $StudentEducation){
            $item = array();
            $item['errorList'] = array();

            // Pflichtfelder:
            $item['dsnr'] = '-leer-';
            $item['School'] = '-leer-';
            if(($tblCompany = $StudentEducation->getServiceTblCompany())){
                // Schultyp erst für dsnr wichtig
                if(($tblSchoolList = School::useService()->getSchoolByCompany($tblCompany)) && ($tblSchoolType = $StudentEducation->getServiceTblSchoolType())){
                    foreach($tblSchoolList as $tblSchool){
                        if($tblSchool->getServiceTblType()->getId() == $tblSchoolType->getId()){
                            $item['dsnr'] = $tblSchool->getSchoolCode();
                        }
                    }
                }
                $item['School'] = $tblCompany->getName();
            }
            $item['Year'] = '-leer-';
            $tblYear = false;
            if(($tblYearList = Term::useService()->getYearByNow())){
                $tblYear = current($tblYearList);
            }
            if($tblYear){
                $item['Year'] = $tblYear->getYearFullString();
            }

            $item['Division'] = '-leer-';
            $item['DivisionUuid'] = '-leer-';
            if(($tblDivision = $StudentEducation->getTblDivision())){
                $item['Division'] = $tblDivision->getName();
                $item['DivisionUuid'] = $tblDivision->getUuid();
            }
            $item['CoreGroup'] = '-leer-';
            if(($tblCoreGroup = $StudentEducation->getTblCoreGroup())){
                $item['CoreGroup'] = $tblCoreGroup->getName();
                if($item['Division'] == '-leer-'){
                    $item['Division'] = $item['CoreGroup'];
                }
                if($item['DivisionUuid'] == '-leer-'){
                    $item['DivisionUuid'] = $tblCoreGroup->getUuid();
                }
            }
            $item['Level'] = $StudentEducation->getLevel();

            $item['uuid'] = '-leer-';
            $item['name'] = '-leer-';
            $item['firstName'] = '-leer-';
            $item['gender'] = '-leer-';
            $item['genderCompare'] = '-leer-';
            $item['dateOfBirth'] = '-leer-';
            $item['contacts'] = '-leer-';
            $item['address'] = '-leer-';

            $item['street'] = '-leer-';
            $item['houseNumber'] = '-leer-';
            $item['country'] = '-leer-';
            $item['countryCompare'] = '-leer-';
            $item['postalCode'] = '-leer-';
            $item['city'] = '-leer-';

            $tblPerson = $StudentEducation->getServiceTblPerson();
            if($tblPerson){
                $item['uuid'] = $tblPerson->getUuid();
                $item['name'] = $tblPerson->getLastName();
                $item['firstName'] = $tblPerson->getFirstName();
                if(($tblCommon = $tblPerson->getCommon())){

                    if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                        $item['dateOfBirth'] = $tblCommonBirthDates->getBirthday('Y-m-d');
                        if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                            $item['gender'] = $tblCommonGender->getName();
                            $item['genderCompare'] = $tblCommonGender->getId(); // ToDO Tabelle hinterlegen https://web1.extranet.sachsen.de/svsp/public/schnittstellen/K101.xml
                        }
                    }
                }
                //ToDo Kontakte

                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
                    $item['address'] = $tblAddress->getGuiString();
                    $item['street'] = $tblAddress->getStreetName();
                    $item['houseNumber'] = $tblAddress->getStreetNumber();
                    $item['country'] = $tblAddress->getNation();
                    $item['countryCompare'] = $tblAddress->getGuiString();  // ToDO Tabelle hinterlegen https://web1.extranet.sachsen.de/svsp/public/schnittstellen/StLa02.xml
                    if($tblCity = $tblAddress->getTblCity()){
                        $item['postalCode'] = $tblCity->getCode();
                        $item['city'] = $tblCity->getName();
                    }
                }
            }

            array_push($TableContent, $item);
        }

        return new TableData($TableContent, null, array(
            'dsnr' => 'DsNr.',
            'School' => 'Schule',
            'Year' => 'Schuljahr',
            'DivisionUuid' => 'Klasse Uuid',    // Klasse UUID v4
            'Level' => 'Stufe',
            'Division' => 'Klasse',
            'uuid' => 'Schüler Uuid',           // Schüler UUID v4
            'name' => 'Name',                   // max 150 Zeichen
            'firstName' => 'Vorname',           // max 150 Zeichen
            'gender' => 'Geschlecht',
            'genderCompare' => 'Geschlecht Ext',// https://web1.extranet.sachsen.de/svsp/public/schnittstellen/K101.xml
            'dateOfBirth' => 'Geb.',            // YYYY-MM-DD
            'contacts' => 'Kontakt',            // Liste von Kontaktpersonen (1-3 Einträge)
            'address' => 'Adresse',             // Aktuelle Adresse des Schülers
        ));
    }
}