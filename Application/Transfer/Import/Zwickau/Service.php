<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.02.2017
 * Time: 08:42
 */

namespace SPHERE\Application\Transfer\Import\Zwickau;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Zwickau
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @return IFormInterface|Danger|Success|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createCompaniesFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'E.nummer' => null,
                    'Einrichtungsname' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Ort' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countCompany = 0;

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $companyName = trim($Document->getValue($Document->getCell($Location['Einrichtungsname'],
                            $RunY)));

                        if ($companyName) {
                            $tblCompany = Company::useService()->insertCompany(
                                $companyName,
                                trim($Document->getValue($Document->getCell($Location['E.nummer'], $RunY)))
                            );
                            if ($tblCompany) {
                                $countCompany++;

                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON'),
                                    $tblCompany
                                );
                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL'),
                                    $tblCompany
                                );

                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                $zipCode = trim($Document->getValue($Document->getCell($Location['Plz'],
                                    $RunY)));
                                $city = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));

                                if ($streetName && $streetNumber && $zipCode && $city) {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany,
                                        $streetName,
                                        $streetNumber,
                                        $zipCode,
                                        $city,
                                        '',
                                        ''
                                    );
                                }
                            }
                        }
                    }
                    return
                        new Success('Es wurden ' . $countCompany . ' Schulen erfolgreich angelegt.');
                } else {
                    Debugger::screenDump($Location);
                    return new Info(json_encode($Location)) .
                        new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param $IsNextYear
     *
     * @return IFormInterface|Danger|string
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $IsNextYear
    ) {


        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Schüler_Schülernummer' => null,
                    'Schüler_Name' => null,
                    'Schüler_Vorname' => null,
                    'Schüler_Klasse' => null,
                    'Schüler_Geschlecht' => null,
                    'Schüler_Staatsangehörigkeit' => null,
                    'Schüler_Straße' => null,
                    'Schüler_Plz' => null,
                    'Schüler_Wohnort' => null,
                    'Schüler_Ortsteil' => null,
                    'Schüler_Landkreis' => null,
                    'Schüler_Bundesland' => null,
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Geschwister' => null,
                    'Schüler_Integr_Förderschüler' => null,
                    'Schüler_Konfession' => null,
                    'Schüler_Einschulung_am' => null,
                    'Schüler_Aufnahme_am' => null,
                    'Schüler_Abgang_am' => null,
                    'Schüler_Schulpflicht_beginnt_am' => null,
                    'Schüler_Schulpflicht_endet_am' => null,
                    'Schüler_abgebende_Schule_ID' => null,
                    'Schüler_aufnehmende_Schule_ID' => null,
                    'Schüler_Außerunterrichtliche_Betr' => null,
                    'Schüler_letzte_Schulart' => null,
                    'Schüler_Krankenversicherung_bei' => null,
                    'Schüler_Krankenkasse' => null,
                    'Schüler_Förderschwerpunkt' => null,
                    'Kommunikation_Telefon1' => null,
                    'Kommunikation_Telefon2' => null,
                    'Kommunikation_Telefon3' => null,
                    'Kommunikation_Telefon4' => null,
                    'Kommunikation_Telefon5' => null,
                    'Kommunikation_Telefon6' => null,
                    'Kommunikation_Fax' => null,
                    'Kommunikation_Email' => null,
                    'Kommunikation_Email1' => null,
                    'Kommunikation_Email2' => null,
                    'Sorgeberechtigter1_Titel' => null,
                    'Sorgeberechtigter1_Name' => null,
                    'Sorgeberechtigter1_Vorname' => null,
                    'Sorgeberechtigter1_Geschlecht' => null,
                    'Sorgeberechtigter1_Straße' => null,
                    'Sorgeberechtigter1_Plz' => null,
                    'Sorgeberechtigter1_Wohnort' => null,
                    'Sorgeberechtigter1_Ortsteil' => null,
                    'Sorgeberechtigter2_Titel' => null,
                    'Sorgeberechtigter2_Name' => null,
                    'Sorgeberechtigter2_Vorname' => null,
                    'Sorgeberechtigter2_Geschlecht' => null,
                    'Sorgeberechtigter2_Straße' => null,
                    'Sorgeberechtigter2_Plz' => null,
                    'Sorgeberechtigter2_Wohnort' => null,
                    'Sorgeberechtigter2_Ortsteil' => null,
                    'Fächer_Bildungsgang' => null,
                    'Fächer_Religionsunterricht' => null,
                    'Fächer_Fremdsprache1' => null,
                    'Fächer_Fremdsprache1_von' => null,
                    'Fächer_Fremdsprache1_bis' => null,
                    'Fächer_Fremdsprache2' => null,
                    'Fächer_Fremdsprache2_von' => null,
                    'Fächer_Fremdsprache2_bis' => null,
                    'Zusatzfeld1' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStudent = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Schüler_Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Schüler_Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                // Außerunterrichtliche Betreuung
                                $group = trim($Document->getValue($Document->getCell($Location['Schüler_Außerunterrichtliche_Betr'],
                                    $RunY)));
                                if ($group != '') {
                                    $tblGroup = Group::useService()->insertGroup($group);
                                    if ($tblGroup) {
                                        Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                    }
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsdatum'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $birthday = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $birthday = '';
                                }

                                $remark = '';

                                $day = trim($Document->getValue($Document->getCell($Location['Schüler_Schulpflicht_endet_am'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $date = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $date = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Schulpflicht_endet_am Datum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $date = '';
                                }
                                if ($date != '') {
                                    $remark .= 'Schulpflicht endet am: ' . $date;
                                }

                                $addition = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'],
                                    $RunY)));
                                if ($addition != '') {
                                    $remark .= ($remark == '' ? '' : " \n") . 'Zusatzfeld1: ' . $addition;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                // division
                                $tblSchoolType = false;
                                $tblDivision = false;
                                if ($IsNextYear) {
                                    $year = 17;
                                    $division = '1';
                                } else {
                                    $year = 16;
                                    $division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                        $RunY)));
                                }
                                if ($division !== '') {
                                    $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
                                    if ($tblYear) {
                                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                                        if (!$tblPeriodList) {
                                            // firstTerm
                                            $tblPeriod = Term::useService()->insertPeriod(
                                                '1. Halbjahr',
                                                '01.08.20' . $year,
                                                '31.01.20' . ($year + 1)
                                            );
                                            if ($tblPeriod) {
                                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                            }

                                            // secondTerm
                                            $tblPeriod = Term::useService()->insertPeriod(
                                                '2. Halbjahr',
                                                '01.02.20' . ($year + 1),
                                                '31.07.20' . ($year + 1)
                                            );
                                            if ($tblPeriod) {
                                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                            }
                                        }

                                        if (strlen($division) > 1) {
                                            $pos = 1;
                                            $level = substr($division, 0, $pos);
                                            $division = trim(substr($division, $pos));
                                        } else {
                                            $level = $division;
                                            $division = '';
                                        }

                                        if ($level < 5) {
                                            $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                        } else {
                                            $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                                        }

                                        $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                                        if ($tblLevel) {
                                            $tblDivision = Division::useService()->insertDivision(
                                                $tblYear,
                                                $tblLevel,
                                                $division
                                            );
                                        }
                                    }

                                    if ($tblDivision) {
                                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                    }
                                }

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $studentCityName = trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY)));
                                $studentCityDistrict = trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
                                    $RunY)));
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                $county = trim($Document->getValue($Document->getCell($Location['Schüler_Landkreis'],
                                    $RunY)));
                                if (trim($Document->getValue($Document->getCell($Location['Schüler_Bundesland'],
                                        $RunY))) == 'SN'
                                ) {
                                    $tblState = Address::useService()->getStateByName('Sachsen');
                                } else {
                                    $tblState = false;
                                }
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $studentCityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $studentCityName,
                                        $studentCityDistrict, '', $county, '', $tblState ? $tblState : null
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Name'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Vorname'],
                                    $RunY)));

                                $fatherCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Plz'],
                                        $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                if ($fatherLastName != '') {

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $fatherCityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $gender = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Geschlecht'],
                                            $RunY)));
                                        if ($gender == 'm') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                            $tblSalutation = Person::useService()->getSalutationById(1);
                                        } elseif ($gender == 'w') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                            $tblSalutation = Person::useService()->getSalutationById(2);
                                        } else {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                            $tblSalutation = null;
                                        }

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Titel'],
                                                $RunY))),
                                            $fatherFirstName,
                                            '',
                                            $fatherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        if ($tblPersonFather) {
                                            Common::useService()->insertMeta(
                                                $tblPersonFather,
                                                '',
                                                '',
                                                $gender,
                                                '',
                                                '',
                                                TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                                '',
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFather,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte2 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Name'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Vorname'],
                                    $RunY)));
                                $motherCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Plz'],
                                        $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                if ($motherLastName != '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
                                    );

                                    if (!$tblPersonMotherExists) {
                                        $gender = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Geschlecht'],
                                            $RunY)));
                                        if ($gender == 'm') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                            $tblSalutation = Person::useService()->getSalutationById(1);
                                        } elseif ($gender == 'w') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                            $tblSalutation = Person::useService()->getSalutationById(2);
                                        } else {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                            $tblSalutation = null;
                                        }

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Titel'],
                                                $RunY))),
                                            $motherFirstName,
                                            '',
                                            $motherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        if ($tblPersonMother) {
                                            Common::useService()->insertMeta(
                                                $tblPersonMother,
                                                '',
                                                '',
                                                $gender,
                                                '',
                                                '',
                                                TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                                '',
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMother,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                if ($tblPersonFather !== null) {
                                    $streetName = '';
                                    $streetNumber = '';
                                    $street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $street, $matches)) {
                                        $pos = strpos($street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $streetName = trim(substr($street, 0, $pos));
                                            $streetNumber = trim(substr($street, $pos));
                                        }
                                    }

                                    $city = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Wohnort'],
                                        $RunY)));

                                    if ($streetName !== '' && $streetNumber !== '' && $fatherCityCode && $city) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather,
                                            $streetName,
                                            $streetNumber,
                                            $fatherCityCode,
                                            $city,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Ortsteil'],
                                                $RunY))),
                                            ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigte2 wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                    }
                                }
                                if ($tblPersonMother !== null) {
                                    $streetName = '';
                                    $streetNumber = '';
                                    $street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $street, $matches)) {
                                        $pos = strpos($street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $streetName = trim(substr($street, 0, $pos));
                                            $streetNumber = trim(substr($street, $pos));
                                        }
                                    }

                                    $city = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Wohnort'],
                                        $RunY)));

                                    if ($streetName !== '' && $streetNumber !== '' && $fatherCityCode && $city) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother,
                                            $streetName,
                                            $streetNumber,
                                            $motherCityCode,
                                            $city,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Ortsteil'],
                                                $RunY))),
                                            ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigte1 wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                    }
                                }

                                for ($i = 1; $i <= 6; $i++) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon' . $i],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        if ($i == 1) {
                                            $tblType = Phone::useService()->getTypeById(5);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(6);
                                            }

                                            Phone::useService()->insertPhoneToPerson(
                                                $tblPerson,
                                                $phoneNumber,
                                                $tblType,
                                                ''
                                            );
                                        } elseif ($i == 2) {
                                            $tblType = Phone::useService()->getTypeById(1);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(2);
                                            }

                                            Phone::useService()->insertPhoneToPerson(
                                                $tblPerson,
                                                $phoneNumber,
                                                $tblType,
                                                ''
                                            );

                                            if ($tblPersonFather) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonFather,
                                                    $phoneNumber,
                                                    $tblType,
                                                    ''
                                                );
                                            }
                                            if ($tblPersonMother) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonMother,
                                                    $phoneNumber,
                                                    $tblType,
                                                    ''
                                                );
                                            }
                                        } elseif ($i == 3 || $i == 5) {
                                            $tblType = Phone::useService()->getTypeById(1);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(2);
                                            }

                                            if ($tblPersonMother) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonMother,
                                                    $phoneNumber,
                                                    $tblType,
                                                    ''
                                                );
                                            }
                                        } elseif ($i == 4 || $i == 6) {
                                            $tblType = Phone::useService()->getTypeById(1);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(2);
                                            }

                                            if ($tblPersonFather) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonFather,
                                                    $phoneNumber,
                                                    $tblType,
                                                    ''
                                                );
                                            }
                                        }
                                    }
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email1'],
                                    $RunY)));
                                if ($mailAddress != '' && $tblPersonMother) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPersonMother,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email2'],
                                    $RunY)));
                                if ($mailAddress != '' && $tblPersonFather) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPersonFather,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $faxNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Fax'],
                                    $RunY)));
                                if ($faxNumber != '') {
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $faxNumber,
                                        Phone::useService()->getTypeById(7),
                                        ''
                                    );
                                }

                                /*
                                 * student
                                 */
                                $sibling = trim($Document->getValue($Document->getCell($Location['Schüler_Geschwister'],
                                    $RunY)));
                                $tblSiblingRank = false;
                                if ($sibling !== '') {
                                    if ($sibling == '0') {
                                        // do nothing
                                    } elseif ($sibling == '1') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(1);
                                    } elseif ($sibling == '2') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(2);
                                    } elseif ($sibling == '3') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(3);
                                    } elseif ($sibling == '4') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(4);
                                    } elseif ($sibling == '5') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(5);
                                    } elseif ($sibling == '6') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(6);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                    }
                                }
                                $tblStudentBilling = null;
                                if ($tblSiblingRank) {
                                    $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                                } else {
                                    $tblStudentBilling = null;
                                }

                                $coachingRequired = (trim($Document->getValue($Document->getCell($Location['Schüler_Integr_Förderschüler'],
                                        $RunY))) == '1');
                                if ($coachingRequired) {
                                    $tblStudentIntegration = Student::useService()->insertStudentIntegration(
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        true
                                    );
                                } else {
                                    $tblStudentIntegration = null;
                                }

                                // Versicherungsstatus passt nicht zu unserem Status
                                $insurance = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenkasse'],
                                    $RunY)));
                                if ($insurance) {
                                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                        '',
                                        '',
                                        $insurance
                                    );
                                } else {
                                    $tblStudentMedicalRecord = null;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Schüler_Schulpflicht_beginnt_am'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $date = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $date = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Schulpflicht_beginnt_am Datum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $date = '';
                                }
                                $tblStudent = Student::useService()->insertStudent($tblPerson,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Schülernummer'],
                                        $RunY))),
                                    $tblStudentMedicalRecord, null,
                                    $tblStudentBilling, null, null, $tblStudentIntegration, null, $date);

                                if ($tblStudent) {

                                    // Schülertransfer
                                    $day = trim($Document->getValue($Document->getCell($Location['Schüler_Einschulung_am'],
                                        $RunY)));
                                    if ($day !== '') {
                                        try {
                                            $enrollmentDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        } catch (\Exception $ex) {
                                            $enrollmentDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Einschulung_am Datum: ' . $ex->getMessage();
                                        }

                                    } else {
                                        $enrollmentDate = '';
                                    }
                                    if ($enrollmentDate !== '') {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            null,
                                            $enrollmentDate,
                                            ''
                                        );
                                    }

                                    $day = trim($Document->getValue($Document->getCell($Location['Schüler_Aufnahme_am'],
                                        $RunY)));
                                    if ($day !== '') {
                                        try {
                                            $arriveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        } catch (\Exception $ex) {
                                            $arriveDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Aufnahme_am Datum: ' . $ex->getMessage();
                                        }

                                    } else {
                                        $arriveDate = '';
                                    }
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                        $RunY)));
                                    $lastSchoolType = trim($Document->getValue($Document->getCell($Location['Schüler_letzte_Schulart'],
                                        $RunY)));
                                    if ($lastSchoolType == 'MS'
                                        || $lastSchoolType == 'RS'
                                    ) {
                                        $tblLastSchoolType = Type::useService()->getTypeById(8); // Oberschule
                                    } elseif ($lastSchoolType === 'GS') {
                                        $tblLastSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                    } else {
                                        $tblLastSchoolType = false;
                                    }
                                    if ($company != '') {
                                        $company = str_pad(
                                            $company,
                                            2,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        $arriveSchool = Company::useService()->getCompanyByDescription($company);
                                    }

                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        $arriveSchool ? $arriveSchool : null,
                                        $tblLastSchoolType ? $tblLastSchoolType : null,
                                        null,
                                        $arriveDate,
                                        ''
                                    );
                                    $day = trim($Document->getValue($Document->getCell($Location['Schüler_Abgang_am'],
                                        $RunY)));
                                    if ($day == '') {
                                        $day = trim($Document->getValue($Document->getCell($Location['Schüler_Schulpflicht_endet_am'],
                                            $RunY)));
                                    }
                                    if ($day !== '') {
                                        try {
                                            $leaveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        } catch (\Exception $ex) {
                                            $leaveDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Abgang_am Datum: ' . $ex->getMessage();
                                        }

                                    } else {
                                        $leaveDate = '';
                                    }
                                    $leaveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_aufnehmende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $company = str_pad(
                                            $company,
                                            2,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        $leaveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        $leaveSchool ? $leaveSchool : null,
                                        null,
                                        null,
                                        $leaveDate,
                                        ''
                                    );

                                    $tblCourse = null;
                                    if (($course = trim($Document->getValue($Document->getCell($Location['Fächer_Bildungsgang'],
                                        $RunY))))
                                    ) {
                                        if ($course == 'HS') {
                                            $tblCourse = Course::useService()->getCourseById(1); // Hauptschule
                                        } elseif ($course == 'GY') {
                                            $tblCourse = Course::useService()->getCourseById(3); // Gymnasium
                                        } elseif ($course == 'RS' || $course == 'ORS') {
                                            $tblCourse = Course::useService()->getCourseById(2); // Realschule
                                        } elseif ($course == '') {
                                            // do nothing
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bildungsgang nicht gefunden.';
                                        }
                                    }
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        $tblSchoolType ? $tblSchoolType : null,
                                        $tblCourse ? $tblCourse : null,
                                        null,
                                        ''
                                    );

                                    /*
                                     * Fächer
                                     */
                                    // Religion
                                    $subjectReligion = trim($Document->getValue($Document->getCell($Location['Fächer_Religionsunterricht'],
                                        $RunY)));
                                    $tblSubject = false;
                                    if ($subjectReligion !== '') {
                                        if ($subjectReligion === 'ETH') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('ETH');
                                        } elseif ($subjectReligion === 'RE/e') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
                                        }
                                        if ($tblSubject) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubject
                                            );
                                        }
                                    }

                                    // Fremdsprachen
                                    for ($i = 1; $i <= 2; $i++) {
                                        $subjectLanguage = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i],
                                            $RunY)));
                                        $tblSubject = false;
                                        if ($subjectLanguage !== '') {
                                            if ($subjectLanguage === 'EN'
                                                || $subjectLanguage === 'Englisch'
                                            ) {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                            } elseif ($subjectLanguage === 'FR'
                                                || $subjectLanguage === 'Fra'
                                                || $subjectLanguage === 'Französisch'
                                            ) {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('FR');
                                            }
                                            if ($tblSubject) {
                                                $tblFromLevel = false;
                                                $fromLevel = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i . '_von'],
                                                    $RunY)));
                                                if ($fromLevel !== '') {
                                                    $tblFromLevel = Division::useService()->insertLevel(
                                                        $tblSchoolType,
                                                        $fromLevel
                                                    );
                                                }

                                                $tblToLevel = false;
                                                $toLevel = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i . '_bis'],
                                                    $RunY)));
                                                if ($toLevel !== '') {
                                                    $tblToLevel = Division::useService()->insertLevel(
                                                        $tblSchoolType,
                                                        $toLevel
                                                    );
                                                }

                                                Student::useService()->addStudentSubject(
                                                    $tblStudent,
                                                    Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                    Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                    $tblSubject,
                                                    $tblFromLevel ? $tblFromLevel : null,
                                                    $tblToLevel ? $tblToLevel : null
                                                );
                                            }
                                        }
                                    }

                                    /*
                                     * Förderung
                                     */
                                    $focus = trim($Document->getValue($Document->getCell($Location['Schüler_Förderschwerpunkt'],
                                        $RunY)));
                                    if ($focus !== '') {
                                        if ($focus === 'HÖ') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Hören');
                                            Student::useService()->addStudentFocus($tblStudent,
                                                $tblStudentFocusType);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    // delete company description (fuxschool id)
                    $tblCompanyAll = Company::useService()->getCompanyAll();
                    if ($tblCompanyAll) {
                        foreach ($tblCompanyAll as $tblCompany) {
                            Company::useService()->updateCompanyWithoutForm($tblCompany, $tblCompany->getName(),
                                $tblCompany->getExtendedName(), '');
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Sorgeberechtigte2 erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Sorgeberechtigte2 exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Sorgeberechtigte1 erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Sorgeberechtigte1 exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedPersonsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Schuljahr' => null,
                    'Klassenstufe' => null,
                    'Sorgeber 1 Vorname' => null,
                    'Sorgeber 1 Nachname' => null,
                    'Sorgeber 2 Vorname' => null,
                    'Sorgeber 2 Nachname' => null,
                    'Adresse' => null,
                    'Postleitzahl' => null,
                    'Ort' => null,
                    'Telefonnummer' => null,
                    'jüngere Geschwister' => null,
                    'Kind Nachname' => null,
                    'Kind Vorname' => null,
                    'geboren am' => null,
                    'eingegangen' => null,
                    'Bemerkung' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countInterestedPerson = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Kind Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Kind Nachname'], $RunY)));

                        if ($firstName !== '' && $lastName !== '') {
                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT')
                                )
                            );

                            if ($tblPerson !== false) {
                                $countInterestedPerson++;

                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Postleitzahl'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityDistrict = '';
                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos + 4));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }
                                $StreetName = '';
                                $StreetNumber = '';
                                $Street = trim($Document->getValue($Document->getCell($Location['Adresse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));
                                    }
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['geboren am'],
                                    $RunY)));
                                if ($day !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $birthday = '';
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $remark = '';
                                $info = trim($Document->getValue($Document->getCell($Location['jüngere Geschwister'],
                                    $RunY)));
                                if ($info !== '') {
                                    $remark = 'jüngere Geschwister: ' . $info;
                                }
                                $info = trim($Document->getValue($Document->getCell($Location['Bemerkung'], $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n") . 'Bemerkung: ' . $info;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['eingegangen'],
                                    $RunY)));
                                if ($day !== '') {
                                    $reservationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $reservationDate = '';
                                }

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    $reservationDate,
                                    '',
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Klassenstufe'], $RunY))),
                                    null,
                                    null,
                                    null,
                                    $remark
                                );

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Custody1
                                $tblPersonCustody1 = null;
                                $firstNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorgeber 1 Vorname'],
                                    $RunY)));
                                $lastNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorgeber 1 Nachname'],
                                    $RunY)));

                                if ($firstNameCustody1 !== '' && $lastNameCustody1 !== '') {
                                    $tblPersonCustody1Exists = Person::useService()->existsPerson(
                                        $firstNameCustody1,
                                        $lastNameCustody1,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody1Exists) {
                                        $tblPersonCustody1 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody1,
                                            '',
                                            $lastNameCustody1,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        // Address
                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody1, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen1 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countFatherExists++;
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                // Custody2
                                $tblPersonCustody2 = null;
                                $firstNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorgeber 2 Vorname'],
                                    $RunY)));
                                $lastNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorgeber 2 Nachname'],
                                    $RunY)));

                                if ($firstNameCustody2 !== '' && $lastNameCustody2 !== '') {
                                    $tblPersonCustody2Exists = Person::useService()->existsPerson(
                                        $firstNameCustody2,
                                        $lastNameCustody2,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody2Exists) {
                                        $tblPersonCustody2 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody2,
                                            '',
                                            $lastNameCustody2,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody2, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen2 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countMotherExists++;
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Interessenten wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                }

                                /*
                                * Phone
                                */
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefonnummer'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(';', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phone, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countInterestedPerson . ' Intessenten erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Sorgeberechtigte1 erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Sorgeberechtigte1 exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Sorgeberechtigte2 erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Sorgeberechtigte2 exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));
                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}