<?php
namespace SPHERE\Application\Transfer\SaxSVS\Export;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Export
 * @package SPHERE\Application\Transfer\SaxSVS\Export
 */
class Export implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('SaxSVS'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return void
     */
    public static function useFrontend()
    {

    }

    /**
     * @param string $CompanyId
     * @param string $SchoolTypeId
     *
     * @return Stage
     */
    public function frontendDashboard(string $CompanyId = '',string $SchoolTypeId = ''): Stage
    {

        $Stage = new Stage('SaxSVS', 'Datentransfer');
        $tblCompany = Company::useService()->getCompanyById($CompanyId);
        $tblSchoolType = Type::useService()->getTypeById($SchoolTypeId);
        if($tblCompany && $tblSchoolType){
            $Test = Export::useService()->getCompleteJsonByCompanyAndType($tblCompany, $tblSchoolType);
//            Debugger::devDump($JSON);
        }


        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $this->getButtonNavigation()
                ),
                new LayoutColumn(
                    ($tblCompany && $tblSchoolType
                        ? new Title($tblCompany->getName().' - '.$tblSchoolType->getName())
                        : '')
                ),
            ))))
        );
        return $Stage;
    }

    public function getButtonNavigation()
    {

        $Buttonlist = array();
        $tblSchoolList = School::useService()->getSchoolAll();
        foreach($tblSchoolList as $tblSchool){
            if(($tblCompany = $tblSchool->getServiceTblCompany())
            && $tblSchoolType = $tblSchool->getServiceTblType()){
                $Buttonlist[] = new Standard($tblCompany->getName().' '.$tblSchoolType->getName(), '/Transfer/SaxSVS/Export', null,
                    array('CompanyId' => $tblCompany->getId(), 'SchoolTypeId' => $tblSchoolType->getId()));
            }
        }
        return implode('', $Buttonlist);
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
                            $item['genderCompare'] = Export::useService()->get; // ToDO Tabelle hinterlegen https://web1.extranet.sachsen.de/svsp/public/schnittstellen/K101.xml
                        }
                    }
                }
                //ToDo Kontakte

                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
                    $item['address'] = $tblAddress->getGuiString();
                    $item['street'] = $tblAddress->getStreetName();
                    $item['houseNumber'] = $tblAddress->getStreetNumber();
                    if($tblCountry = $tblAddress->getTblCountry()){
                        $item['country'] = $tblAddress->getNation();
                        $item['countryExtern'] = $tblCountry->getExtern();
                    }
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
//            'Name' => 'Name',
//            'Company' => 'Institution',
//            'Division' => 'Klasse',
//            'CoreGroup' => 'Stammgruppe',
//            'Level' => 'Stufe',
//            'Schooltype' => 'Schulart'
        ));
    }
}