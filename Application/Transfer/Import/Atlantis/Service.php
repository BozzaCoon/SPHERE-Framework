<?php
namespace SPHERE\Application\Transfer\Import\Atlantis;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType as TblTypeRelationship;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Atlantis
 */
class Service
{
    private $RunY = 0;
    private $Location = array();
    /** @var PhpExcel $Document */
    private $Document = null;

    public array $Subject = array(
        '1532' => 'Englisch',
        '1542' => 'Französisch',
        '1543' => 'Französisch Niveau A',
        '1547' => 'Latein',
        '1555' => 'Italienisch',
        '1559' => 'Chinesisch',
    );
    public array $Nation = array(
        '11' => 'Syrien',
        '22' => 'Türkei',
        '27' => 'Ungarn',
        '38' => 'USA',
        '55' => 'Peru',
        '103' => 'Brasilien',
        '109' => 'Bulgarien',
        '113' => 'China (VR)',
        '118' => 'Deutschland',
        '134' => 'Griechenland',
//        '135' => '', // ToDO Lehrer Hughes, Gillian
        '145' => 'Iran Islam. Rep.',
        '149' => 'Italien',
        '154' => 'Serbien und Montenegro',
        '170' => 'Kroatien',
        '193' => 'Mexiko',
    );
    public array $Religion = array(
        'EV' => 'evangelisch',
        'EVF' => 'EVF',
        'GRO' => 'syrisch-orthodox',
        'IS' => 'islamisch',
        'KE' => 'Keine Religionszugehörigkeit',
        'NA' => 'neuapostolisch',
        'OA' => '(leer)',
        'RK' => 'römisch-katholisch',
    );

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param array|null $Data
     *
     * @return string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(IFormInterface $Form = null, UploadedFile $File = null, array $Data = null): string
    {
        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }
        ini_set('memory_limit', '4G');

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));

            return $Form;
        }
        $MandantAcronym = Account::useService()->getMandantAcronym();
        if(isset($Data['Year']) && !empty($Data['Year'])){
            $YearString = substr($Data['Year'], 0, 4);
        } else {
            // fallback
            $YearString = (new DateTime())->format('Y');
        }

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());

        /**
         * Read
         */
        //$File->getMimeType()
        $this->Document = Document::getDocument($File->getPathname());
        if (!$this->Document instanceof PhpExcel) {
            $Form->setError('File', 'Fehler');

            return $Form;
        }

        $X = $this->Document->getSheetColumnCount();
        $Y = $this->Document->getSheetRowCount();

        /**
         * Header -> Location
         */
        $this->Location = array(
            // Filter auf "aktive Schüler"
        'KlassenKZ' => null,
        'EntlassAusSchul' => null,

        'SchuelerID' => null, // Schülernummer
        'Schuelername' => null, // Nachname
        'Vorname' => null, // Vorname
        'Geburtsname' => null, // Geburtsname
        'Geschlecht' => null, // als 0 => junge & 1 => mädchen
        'Strasse' => null, // Straße
        'HausNr' => null, // HSN
        'BundLand' => null, // BW als kürzel -> korrekt ausschreiben -> erstmal direkt übernehmen
        'Wohnort' => null, // Ort
        'Wohnortsteil' => null, // Ortsteil
        'PLZ' => null, // PLZ
        'Telefon' => null,
        'HandyNr' => null,
        'Telefax' => null,
        'SchuleMail' => null,
        'NationKZ' => null, // kann man da noch was drauß machen? 118 Deutschland !?
        'NationKZ2' => null,
        'KonfessKnz' => null, // in Verwendung
        'Geburtsort' => null, // in Verwendung
        'Sprache1' => null,
        'Sprache2' => null,
        'Sprache3' => null,
        'SprachfoerderJN' => null,
        'Muttersprache' => null,
        'Geburtsland' => null,
        'SozialEmpfJN' => null,
        'HeimJN' => null,
        'RealschuleJN' => null,
        'LetzteBesKlasse' => null,
        'UnzurDeutschJN' => null,
        'VtNr' => null,
        'ErrBildAbschlus' => null,
        'EinwanderDatum' => null,
        'DatumRelAbmeld' => null,
        'GastschuelerJN' => null,
        'ZugKZ' => null,
        'PrivatEMail' => null,
        'GeburtsKreis' => null,
        'NameBezPers2' => null,
        'VNameBezPers2' => null,
        'StatusBezPers2' => null,
        'StrasseBezugsperson2' => null,
        'HausNrBezugsperson2' => null,
        'PLZBezPers2' => null,
        'OrtBezPers2' => null,
        'TelefonBezPers2' => null,
        'TelefaxBezPers2' => null,
        'MobilTelBezPers2' => null,
        'eMailBezPers2' => null,
        'BundLand_2' => null,
        'LandOrtBP2' => null,
        'NationKZBP2' => null,
        'AnredeBezPers2' => null,
        'TitelBezPers2' => null,
        'Geburtsdatum' => null, // in Verwendung
        'OrtsteilBP2' => null,
        'EStatIDBP2' => null,
        'ESAdresseIDBP2' => null,
        'GeschlBezPers2' => null,
        'BP2SorgeRechtJN' => null,
        'NameBezPers1' => null,
        'VNameBezPers1' => null,
        'StatusBezPers1' => null,
        'StrasseBezugsperson1' => null,
        'HausNrBezugsperson1' => null,
        'PLZBezPers1' => null,
        'OrtBezPers1' => null,
        'TelefonBezPers1' => null,
        'TelefaxBezPers1' => null,
        'MobilTelBezPers1' => null,
        'eMailBezPers1' => null,
        'BundLand_3' => null,
        'LandOrtBP1' => null,
        'NationKZBP1' => null,
        'AnredeBezPers1' => null,
        'TitelBezPers1' => null,
        'OrtsteilBP1' => null,
        'EStatIDBP1' => null,
        'ESAdresseIDBP1' => null,
        'GeschlBezPers1' => null,
        'BP1SorgeRechtJN' => null,
        'Jahrgang' => null,
        'Anrede' => null, // nicht gepflegt
        'Namenszusatz' => null, // leer
        'BlandWohnort' => null, // unnötig
        'Bild' => null,
        'ProfilESID' => null,
        'TeilnReligionIn' => null,
        'Passwort' => null,
        'FirmaId' => null,
        'BerufsNr' => null,
        'BerufsBezeichng' => null,
        'Berufsfeld' => null,
        'Herkunftsart' => null,
        'BeginnAusbild' => null, // nicht gepflegt
        'EndeAusbild' => null,
        'AnlageDatum' => null, // nicht gepflegt
        'DeaktivDatum' => null,
        'DatumEinschul' => null, // nicht gepflegt
        'EintrittKlasse' => null,
        'Vorbildung' => null,
        'AngBildAbschlus' => null,
        'EntlassAusSchul_1' => null,
        'PruefungsDatum' => null,
        'PruefungsNote' => null,
        'PruefungsPunkte' => null,
        'PruefBestandJN' => null,
        'Vertragsbeginn' => null,
        'VorvertragJN' => null,
        'EStatID' => null,
        'ESAdresseID' => null,
        'ESFSFID1' => null,
        'ESFSFID2' => null,
        'ESFSFID3' => null,
        'VorbildArt' => null,
        'VorbildVerbal' => null,
        'Neuanfaenger' => null,
        'SchuelerStatus' => null,
        'BetreuungsUmf' => null,
        'BetreuungsArt' => null,
        'Schulpflicht' => null, // nicht gepflegt
        'PraktikumTage' => null,
        'BenoetigtSonPU' => null,
        'HatSonderPU' => null,
        'PraktikumWochen' => null,
        'ABZeitVerkuerzg' => null,
        'AsylbewerberJN' => null,
        'AussiedlerJN' => null,
        'BaFoegJN' => null,
        'UmschuelerJN' => null,
        'BehinderungJN' => null,
        'ArtBehinderung' => null,
        'PflichtFreiwill' => null,
        'Familienstand' => null,
        'Column1' => null,
        );

        $unKnownColumns = array();
        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($this->Document->getValue($this->Document->getCell($RunX, 0)));
            if (array_key_exists($Value, $this->Location)) {
                $this->Location[$Value] = $RunX;
            } elseif($Value != '') {
                $unKnownColumns[] = $Value . ': ' . new DangerText('Spalte ist im Import nicht enthalten!');
            }
        }
        if (!empty($unKnownColumns)) {
            return new Warning(new Listing($unKnownColumns)) . new Danger(
                "Datei konnte nicht importiert werden, da diese Spalten im Import nicht verfügbar sind.");
        }

//        /*
//         * Es müssen nur die Spalte Name und Vorname vorhanden sein
//         */
//        $MissingColumn = array();
//        if ($this->Location['Name'] === null) {
//            $MissingColumn[] = 'Name: ' . new DangerText('Spalte nicht gefunden!');
//        }
//        if ($this->Location['Vorname'] === null) {
//            $MissingColumn[] = 'Vorname: ' . new DangerText('Spalte nicht gefunden!');
//        }
//        if (!empty($MissingColumn)) {
//            return new Warning(new Listing($MissingColumn)) . new Danger(
//                "Datei konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden.");
//        }

        /**
         * Import
         */
        $countStudent = 0;
        $countS1 = 0;
        $countS2 = 0;
        $countS1Exists = 0;
        $countS2Exists = 0;

        $error = array();
        $info = array();
        for ($this->RunY = 1; $this->RunY < $Y; $this->RunY++) {
            // Bedingungen zum Überspringen der Zeile
            $LeaveDate = $this->getValue('EntlassAusSchul');
            if($LeaveDate != null) {
                continue;
            }
            $courseRemoveList = array('inaktiv', 'unbekannt');
            $courseName = $this->getValue('KlassenKZ');
            if(in_array($courseName, $courseRemoveList)) {
                continue;
            }

            set_time_limit(300);
            // Student ---------------------------------------------------------------------------------------------
            $firstName = $this->getValue('Vorname');
            $lastName = $this->getValue('Schuelername');
            if ($firstName === '' || $lastName === '') {
                $error[] = new DangerText('Zeile: '.($this->RunY + 1)).' Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                continue;
            }
            // person check
            $cityCode = $this->getValue('PLZ');
            $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
            if($tblPerson){
                $error[] = new DangerText('Zeile: '.($this->RunY + 1)).' Schüler '.$tblPerson->getLastFirstName()
                    .' wurde nicht hinzugefügt. "bereits vorhanden"';
                continue;
            }

            $secondName = $this->getValue('Namenszusatz');
            $birthName = $this->getValue('Geburtsname');
//            $callName = $this->getValue('Rufname');
            $callName = '';
            $studentGender = $this->getValue('Geschlecht');
            $tblPerson = $this->setPersonStudent($firstName, $secondName, $callName, $lastName, $birthName, $studentGender, false, $this->RunY + 1);
            $countStudent++;

            // common & birthday
            $studentGender = $this->getValue('Geschlecht');
            $studentBirth = $this->getValue('Geburtsdatum');
            $birthPlace = $this->getValue('Geburtsort');
            $nationality = $this->getValue('NationKZ');
            $nationality = $this->replaceNationality($nationality);
            $denomination = $this->getValue('KonfessKnz');
//            $remarkString = $this->getValue('Bemerkungen');
            $remarkString = ''; // noch etwas hinterlegen?
            // Beispiel um Bemerkung zu erweitern
//            $remark = $this->getValue('Abholberechtigte');
//            if ($remark != '') {
//                $remarkString .= 'Abholberechtigte: ' . $remark;
//            }
            $this->setPersonBirth($tblPerson, $studentBirth, $birthPlace, $studentGender, $nationality, $denomination, $remarkString, $this->RunY, $error);
            // tblChild
//            $AuthorizedToCollect = $this->getValue('Abholberechtigte');
//            if($AuthorizedToCollect){
//                Child::useService()->insertChild($tblPerson, $AuthorizedToCollect);
//            }

            // student
            $Identification = $this->getValue('SchuelerID');
            $schoolAttendanceStartDate = ''; // $this->getValue('Schulpflicht')
            $enrollmentDate = ''; // $this->getValue('DatumEinschul')
            $arriveDate = ''; // $this->getValue('Schulaufnahme_Datum')
            $arriveRemark = '';
            $schoolEnrollmentType = ''; // $this->getValue('Einschulungsart')
            // medicine
            $disease = ''; // $this->getValue('Allergien');
            $medication = ''; // $this->getValue('Medikamente');
            $insurance = ''; // $this->getValue('Krankenkasse');
            $religion = ''; // $this->getValue('Fach_Religion');
            $specialNeedsLevel = ''; // $this->getValue('Förderschule_Stufe');
            $ForeignLanguage1 =  $this->getValue('Sprache1');
            $ForeignLanguage2 =  $this->getValue('Sprache2');
            $MigrationBackground =  $this->getValue('Muttersprache');
            $hasMigrationBackground = false;
            if($MigrationBackground == '' || strtolower($MigrationBackground) == 'deutsch') {
                $MigrationBackground = ''; // deutsch wieder entfernen
            } else {
                $hasMigrationBackground = true;
            }
            $this->setPersonTblStudent($tblPerson, $Identification, $schoolAttendanceStartDate, $arriveDate, $arriveRemark, $disease, $medication,
                $insurance, $religion, $enrollmentDate, null, $schoolEnrollmentType, $specialNeedsLevel, $ForeignLanguage1, $ForeignLanguage2,
                $hasMigrationBackground, $MigrationBackground, $this->RunY, $error);

            // division
            $divisionString = $this->getValue('KlassenKZ');
            $level = $this->getValue('Jahrgang');
            $this->setPersonDivision($tblPerson, $YearString, $divisionString, $level);


            // address
            $streetName = $this->getValue('Strasse');
            $streetNumber = $this->getValue('HausNr');
            $city = $this->getValue('Wohnort');
            $cityCode = $this->getValue('PLZ');
            $district = $this->getValue('Wohnortsteil');
            $country = $this->getValue('BundLand');
            $nation = ''; // $this->getValue('NationKZ');
            $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $country, $nation, $this->RunY, $error);

            // contact
            $Phone = array();
            if(($Telefon = $this->getValue('Telefon'))){
                $Phone[] = $this->parsePhoneField($Telefon);
            }
            if(($Telfax = $this->getValue('HandyNr'))){
                $Phone[] = $this->parsePhoneField($Telfax);
            }
            $this->setPersonPhone($tblPerson, $Phone);

            $privateMail = $this->getValue('SchuleMail');

            // S1 --------------------------------------------------------------------------------------------------
            $firstName_S2 = '';
            $lastName_S2 = '';

            $firstName_S1 = $this->getValue('VNameBezPers1');
            $lastName_S1 = $this->getValue('NameBezPers1');
            $firstName_S2Temp = $this->getValue('VNameBezPers2');
            $lastName_S2Temp = $this->getValue('NameBezPers2');
//            Debugger::devDump(array(
//                'firstName_S1' => $firstName_S1,
//                'lastName_S1' => $lastName_S1,
//                'firstName_S2Temp' => $firstName_S2Temp,
//                'lastName_S2Temp' => $lastName_S2Temp,
//            ));
            $cityCode_S1 = $this->getValue('PLZBezPers1');
            // sonderfall Vor und Nachnamen in einer Spalte aber beide Spalten gepflegt (2 Personen)
            if($firstName_S1 != '' && $lastName_S1 != ''){
                // trennen aus einer Spalte
                $slicePositionV = strpos($firstName_S1, ', ');
                $slicePositionN = strpos($lastName_S1, ', ');
                if($slicePositionV && $slicePositionN){
                    $lastName_S2 = substr($firstName_S1, 0, $slicePositionV);
                    $firstName_S2 = substr($firstName_S1, $slicePositionV + 2);
                    $firstName_S1 = substr($lastName_S1, $slicePositionN + 2);
                    $lastName_S1 = substr($lastName_S1, 0, $slicePositionN);
                } else {
                    // trennen aus einer Spalte
                    $slicePositionV = strpos($firstName_S1, ' ');
                    $slicePositionN = strpos($lastName_S1, ' ');
                    if($slicePositionV && $slicePositionN){
                        // Vorsicht Namen gedreht!
                        $firstName_S2 = substr($lastName_S1, 0, $slicePositionN);
                        $lastName_S2 = substr($lastName_S1, $slicePositionN + 1);
                        $lastName_S1 = substr($firstName_S1, $slicePositionV + 1);
                        $firstName_S1 = substr($firstName_S1, 0, $slicePositionV);
                    }
                }

            }
            // sonderfall Alles in Name gepackt Nachname Vorname
            if($firstName_S1 == '' && $lastName_S1 != ''){
                // trennen aus einer Spalte
                $slicePosition = strpos($lastName_S1, ' ');
                $firstName_S1 = substr($lastName_S1, $slicePosition + 1);
                $lastName_S1 = substr($lastName_S1, 0, $slicePosition);
            }
            // Vorname mit und getrennt (2 Personen)
            if(($slicePosition = strpos($firstName_S1, ' und '))){
                $firstName_S2 = substr($firstName_S1, $slicePosition + 5);
                $firstName_S1 = substr($firstName_S1, 0, $slicePosition);
                $lastName_S2 = $lastName_S1;
            }
            if(($slicePosition = strpos($firstName_S1, ' u. '))){
                $firstName_S2 = substr($firstName_S1, $slicePosition + 4);
                $firstName_S1 = substr($firstName_S1, 0, $slicePosition);
                $lastName_S2 = $lastName_S1;
            }

//            Debugger::devDump(array(
//                'Vorname S1' => $firstName_S1,
//                'Nachname S1' => $lastName_S1,
//                'Vorname S2' => $firstName_S2,
//                'Nachname S2' => $lastName_S2));

            $streetName_S2 = '';
            $streetNumber_S2 = '';
            $city_S2 = '';
            $cityCode_S2 = '';
            $district_S2 = '';
            $country_S2 = '';
            $nation_S2 = '';

            if($firstName_S1 != '' && $lastName_S1 != ''){ // nur vorhandene Datensätze
                $addInformation = true;
                $tblPerson_S1 = Person::useService()->existsPerson($firstName_S1, $lastName_S1, $cityCode_S1);
                if(!$tblPerson_S1)
                {
                    $salutation_S1 = ''; // $this->getValue('S1_Anrede');
                    $title_S1 = ''; // $this->getValue('S1_Titel');
                    $memberNumber_S1 = ''; // $this->getValue('S1_Mitgliedsnummer');
                    $assistance_S1 = ''; // $this->getValue('S1_Mitarbeitbereitschaft');
                    $contactNumber_S1 = ''; // $this->getValue('S1_BC_Kontakt_Nr');
                    $tblPerson_S1 = $this->setPersonCustody($salutation_S1, $title_S1, $firstName_S1, $lastName_S1, $memberNumber_S1, $assistance_S1, $contactNumber_S1);
                    $countS1++;
                } else {
                    $info[] = new Muted(new Small('Zeile: '.($this->RunY + 1).' Der Sorgeberechtigte S1 ('.$lastName_S1.' PLZ '.$cityCode_S1.') wurde nicht angelegt, da schon eine
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                    Person verknüpft'));
                    $countS1Exists++;
                    // keine doppelte Datenpflege
                    $addInformation = false;
                }
                if($addInformation){
//                    // custody
//                    $occupation = ''; // $this->getValue('S1_Beruf');
//                    $employment = ''; // $this->getValue('S1_Arbeitsstelle');
//                    $remark = ''; // $this->getValue('S1_Bemerkungen');
//                    Custody::useService()->insertMeta($tblPerson_S1, $occupation, $employment, $remark);

                    // S1 address
                    $streetName_S1 = $this->getValue('StrasseBezugsperson1');
                    $streetNumber_S1 = $this->getValue('HausNrBezugsperson1');
                    $city_S1 = $this->getValue('OrtBezPers1');
                    $cityCode_S1 = $this->getValue('PLZBezPers1');
                    $district_S1 = $this->getValue('OrtsteilBP1');
                    $country_S1 = ''; // $this->getValue('Landkreis');
                    $nation_S1 = ''; // $this->getValue('S1_Land');
                    // Country übernehmen wenn Schüleradresse identisch ist
                    if($streetName_S1 == $streetName
                    && $streetNumber_S1 == $streetNumber
                    && $city_S1 == $city
                    && $cityCode_S1 == $cityCode
                    ){
                        $country_S1 = $country;
                    }

                    $this->setPersonAddress($tblPerson_S1, $streetName_S1, $streetNumber_S1, $city_S1, $cityCode_S1, $district_S1, $country_S1, $nation_S1, $this->RunY, $error);
                    // S2 if same Column
                    // S2 address
                    $streetName_S2 = $this->getValue('StrasseBezugsperson1');
                    $streetNumber_S2 = $this->getValue('HausNrBezugsperson1');
                    $city_S2 = $this->getValue('OrtBezPers1');
                    $cityCode_S2 = $this->getValue('PLZBezPers1');
                    $district_S2 = $this->getValue('OrtsteilBP1');
                    $country_S2 = ''; // $this->getValue('Landkreis');
                    $nation_S2 = ''; // $this->getValue('S1_Land');

                    // S1 Phone
                    $Phone = array();
                    if(($Telefon = $this->getValue('TelefonBezPers1'))){
                        $Phone[] = $this->parsePhoneField($Telefon);
                    }
                    if(($Telfax = $this->getValue('TelefaxBezPers1'))){
                        $Phone[] = $this->parsePhoneField($Telfax);
                    }
                    if(($Handy = $this->getValue('MobilTelBezPers1'))){
                        $Phone[] = $this->parsePhoneField($Handy);
                    }
                    $this->setPersonPhone($tblPerson_S1, $Phone);
                    // S1 Mail
                    $privateMail = $this->getValue('eMailBezPers1');
                    $this->setPersonMail($tblPerson_S1, $privateMail);
                }

//                $S1_Alleinerziehend = $this->getValue('S1_Alleinerziehend');
                $isSingleParent = false;
//                if(strtoupper($S1_Alleinerziehend) == 'X'){
//                    $isSingleParent = true;
//                }
                // relationship
                $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                Relationship::useService()->insertRelationshipToPerson($tblPerson_S1, $tblPerson, $tblRelationshipType, '', 1, $isSingleParent);
            }

            // S2 --------------------------------------------------------------------------------------------------

            $firstName_S2Temp = $this->getValue('VNameBezPers2');
            $lastName_S2Temp = $this->getValue('NameBezPers2');
            $cityCode_S2Temp = $this->getValue('PLZBezPers2');
            // Nur bei gepflegter Person2 wird Person2 überschreiben (wenn vorhanden)
            if($firstName_S2Temp){
                $firstName_S2 = $firstName_S2Temp;
            }
            if($lastName_S2Temp){
                $lastName_S2 = $lastName_S2Temp;
            }
            if($cityCode_S2Temp){
                $cityCode_S2 = $cityCode_S2Temp;
            } else {
                $cityCode_S2 = $cityCode_S1;
            }

            // nur vorhandene Datensätze
            if($firstName_S2 != '' && $lastName_S2 != ''){
                $addInformation = true;
                $tblPerson_S2 = Person::useService()->existsPerson($firstName_S2, $lastName_S2, $cityCode_S2);
                if(!$tblPerson_S2)
                {
                    $salutation_S2 = ''; // $this->getValue('S2_Anrede');
                    $title_S2 = ''; // $this->getValue('S2_Titel');
                    $memberNumber_S2 = ''; // $this->getValue('S2_Mitgliedsnummer');
                    $assistance_S2 = ''; // $this->getValue('S2_Mitarbeitbereitschaft');
                    $contactNumber_S2 = ''; // $this->getValue('S2_BC_Kontakt_Nr');
                    $tblPerson_S2 = $this->setPersonCustody($salutation_S2, $title_S2, $firstName_S2, $lastName_S2, $memberNumber_S2, $assistance_S2, $contactNumber_S2);
                    $countS2++;
                } else {
                    $info[] = new Muted(new Small('Zeile: '.($this->RunY + 1).' Der Sorgeberechtigte S2 ('.$lastName_S2.' PLZ '.$cityCode_S2.') wurde nicht angelegt, da schon eine
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                    Person verknüpft'));
                    $countS2Exists++;
                    // keine doppelte Datenpflege
                    $addInformation = false;
                }
                if($addInformation){
                    // custody
//                    $occupation = $this->getValue('S1_Beruf');
//                    $employment = $this->getValue('S1_Arbeitsstelle');
//                    $remark = $this->getValue('S1_Bemerkungen');
//                    Custody::useService()->insertMeta($tblPerson_S2, $occupation, $employment, $remark);
                    // S2 address
                    $streetName_S2Temp = $this->getValue('StrasseBezugsperson2');
                    $streetNumber_S2Temp = $this->getValue('HausNrBezugsperson2');
                    $city_S2Temp = $this->getValue('OrtBezPers2');
                    $cityCode_S2Temp = $this->getValue('PLZBezPers2');
                    $district_S2Temp = $this->getValue('OrtsteilBP2');
                    $country_S2Temp = ''; // $this->getValue('Landkreis');
                    $nation_S2Temp = ''; // $this->getValue('S2_Land');
                    // Sobald ein S2 Adressteil gepflegt ist soll die andere Adresse ersetzt werden, auch wenn es keine korrekte Adresse ergibt
                    if($streetName_S2Temp || $streetNumber_S2Temp || $city_S2Temp || $cityCode_S2Temp){
                        $streetName_S2 = $streetName_S2Temp;
                        $streetNumber_S2 = $streetNumber_S2Temp;
                        $city_S2 = $city_S2Temp;
                        $cityCode_S2 = $cityCode_S2Temp;
                        $district_S2 = $district_S2Temp;
                        $country_S2 = $country_S2Temp;
                        $nation_S2 = $nation_S2Temp;
                    }

                    $this->setPersonAddress($tblPerson_S2, $streetName_S2, $streetNumber_S2, $city_S2, $cityCode_S2, $district_S2, $country_S2, $nation_S2, $this->RunY, $error);

                    // S2 Phone
                    $Phone = array();
                    if(($Telefon = $this->getValue('TelefonBezPers2'))){
                        $Phone[] = $this->parsePhoneField($Telefon);
                    }
                    if(($Telfax = $this->getValue('TelefaxBezPers2'))){
                        $Phone[] = $this->parsePhoneField($Telfax);
                    }
                    if(($Handy = $this->getValue('MobilTelBezPers2'))){
                        $Phone[] = $this->parsePhoneField($Handy);
                    }
                    $this->setPersonPhone($tblPerson_S1, $Phone);
                    // S2 Mail
                    $privateMail = $this->getValue('eMailBezPers2');
                    $this->setPersonMail($tblPerson_S1, $privateMail);
                }

//                $S2_Alleinerziehend = $this->getValue('S2_Alleinerziehend');
                $isSingleParent = false;
//                if(strtoupper($S2_Alleinerziehend) == 'X'){
//                    $isSingleParent = true;
//                }
                // relationship
                $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                Relationship::useService()->insertRelationshipToPerson($tblPerson_S2, $tblPerson, $tblRelationshipType, '', 2, $isSingleParent);
            }
        }

        if(empty($error)){
            $error = new SuccessText('Keine');
        }

        $AccordionInfo = new Accordion();
        $AccordionInfo->addItem('Information - Vorhandene Personen', new Listing($info));

        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.', null, false, '2', '5')
                , 4),
                new LayoutColumn(
                    new Success('Es wurden '.$countS1.' Sorgeberechtigte S1 erfolgreich angelegt.'.
                    ($countS1Exists > 0
                        ? new Warning(' ('.$countS1Exists.' dopplungen) ', null, false, '2', '5')
                          .($countS1 + $countS1Exists).' Zuweisungen zu Schülern.'
                        : '')
                    , null, false, '3', '5')
                , 4),
                new LayoutColumn(
                    new Success('Es wurden '.$countS2.' Sorgeberechtigte S2 erfolgreich angelegt.'.
                    ($countS2Exists > 0
                        ? new Warning(' ('.$countS2Exists.' dopplungen) ', null, false, '2', '5')
                          .($countS2 + $countS2Exists).' Zuweisungen zu Schülern.'
                        : '')
                    , null, false, '3', '5')
                , 4),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Fehler',
                        $error,
                        Panel::PANEL_TYPE_DANGER
                    )
                ),
                new LayoutColumn(
                    $AccordionInfo
                )
            ))
        )));
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    private function getValue(string $columnName): string
    {
        if ($this->Location[$columnName] !== null) {
            return trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $this->RunY)));
        }

        return '';
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffFromFile(IFormInterface $Form = null, UploadedFile $File = null): IFormInterface|Danger|string
    {

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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
//                    'Nr'             => null,
//                    'Anrede'         => null,
                    'Titel'          => null, // bis jetzt alles leer
                    'LehrerName'     => null, // Name
                    'LehrerVorname'  => null, // Vorname
                    'Geschlecht'     => null,
                    'NationKZ'       => null,
                    'Konfession'     => null,
//                    'Lehrer'         => null, // sind wohl alles Lehrer
                    'LehrerKZ'       => null, // Kürzel
                    'Geburtstag'     => null, // Geburtsdatum
                    'Geburtsort'     => null,
                    'Geburtsname'    => null,
                    'PrivatPLZ'      => null, // PLZ
                    'Wohnort'        => null, // Ort
//                    'Ortsteil'       => null, // stehen nur zahlen drin
                    'Privatstrasse'  => null, // Straße -> Herrausforderung straße und Hnr. sind kombiniert
                    'HausNr'         => null, // in den 2 Testdaten leer
//                    'Land'           => null, // nicht sinnvoll gepflegt
                    'Schultelefon'   => null, // Geschäftlich_Festnetz
                    'PrivatTelefon'  => null, // Privat_Festnetz
                    'PrivatHandy'    => null, // Privat_Mobil
                    'PrivatFax'      => null,
                    'privateEMail'   => null, // E_Mail_Privat
                    'PersonalNummer' => null,

                    // mögliche Daten für Remark noch außen vor:
                        //  LehrerAmtsbez
                        //  Familienstand
                        //  Lehrerzimmer

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
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Teacher ---------------------------------------------------------------------------------------------
                        $firstName = trim($Document->getValue($Document->getCell($Location['LehrerVorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['LehrerName'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = new DangerText('Zeile: '.($RunY + 1)).' Mitarbeiter wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                            continue;
                        }
                        // person check
                        $cityCode = trim($Document->getValue($Document->getCell($Location['PrivatPLZ'], $RunY)));
                        $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
                        if($tblPerson){
                            $info[] = new Muted(new Small('Zeile: '.($RunY + 1).' Person '.$tblPerson->getLastFirstName().' gefunden, wird zusätzlich Mitarbeiter/Lehrer.'));
                            $countStaffExists++;
                            $this->setGroupStaff($tblPerson);
                            $remark = '';
                            if(($PersonalNumber = trim($Document->getValue($Document->getCell($Location['PersonalNummer'], $RunY))))){
                                $remark = 'Personalnummer: '.$PersonalNumber;
                            }
                            if($remark){
                                $this->setUpdateCommonRemark($tblPerson, $remark);
                            }
                            // add teacher info
                            if(($acronym = trim($Document->getValue($Document->getCell($Location['LehrerKZ'], $RunY))))){
                                Teacher::useService()->insertTeacher($tblPerson, $acronym);
                            }
                        } else {
                            // nicht vorhandene Personen werden angelegt
                            $salutation = ''; // trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                            $title = trim($Document->getValue($Document->getCell($Location['Titel'], $RunY)));
                            $BirthName = trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)));
                            $BirthName = str_replace('pipi', '', $BirthName);
                            $tblPerson = $this->setPersonStaff($salutation, $title, $firstName, $lastName, $BirthName);

                            $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                            $birthPlace = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
                            $nationality = trim($Document->getValue($Document->getCell($Location['NationKZ'], $RunY)));
                            $nationality = $this->replaceNationality($nationality);
                            $birth = trim($Document->getValue($Document->getCell($Location['Geburtstag'], $RunY)));
                            $denomination = trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY)));

                            $remark = 'Personalnummer: '.trim($Document->getValue($Document->getCell($Location['PersonalNummer'], $RunY)));
                            $this->setPersonBirth($tblPerson, $birth, $birthPlace, $gender, $nationality, $denomination, $remark, $RunY, $error);

                            // address
                            // straße und Hnr. sind kombiniert
                            $streetName = trim($Document->getValue($Document->getCell($Location['Privatstrasse'], $RunY)));
                            $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr'], $RunY)));
                            if(!$streetNumber){
                                $AddressArray = $this->splitStreetAndNumber($streetName);
                                $streetName = $AddressArray['street'];
                                $streetNumber = $AddressArray['number'];
                            }

                            $city = trim($Document->getValue($Document->getCell($Location['Wohnort'], $RunY)));
                            $cityCode = trim($Document->getValue($Document->getCell($Location['PrivatPLZ'], $RunY)));
                            $district = ''; //trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));

                            $nation = ''; // trim($Document->getValue($Document->getCell($Location['Land'], $RunY)));
                            $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, '', $nation, $RunY, $error);
                            $countStaff++;
                        }

                        if(($privatePhone = trim($Document->getValue($Document->getCell($Location['PrivatTelefon'], $RunY))))){
                            $this->setPersonPhoneWithType($tblPerson, $privatePhone, TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_PHONE);
                        }
                        if(($privateMobile = trim($Document->getValue($Document->getCell($Location['PrivatHandy'], $RunY))))){
                            $this->setPersonPhoneWithType($tblPerson, $privateMobile, TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
                        }
                        if(($businessPhone = trim($Document->getValue($Document->getCell($Location['Schultelefon'], $RunY))))){
                            $this->setPersonPhoneWithType($tblPerson, $businessPhone, TblTypePhone::VALUE_NAME_BUSINESS, TblTypePhone::VALUE_DESCRIPTION_PHONE);
                        }
                        if(($privateFax = trim($Document->getValue($Document->getCell($Location['PrivatFax'], $RunY))))){
                            $this->setPersonPhoneWithType($tblPerson, $privateFax, TblTypePhone::VALUE_NAME_FAX, TblTypePhone::VALUE_NAME_PRIVATE);

                        }

                        // mail
                        if(($privateMail = trim($Document->getValue($Document->getCell($Location['privateEMail'], $RunY))))){
                            $this->setPersonMail($tblPerson, $privateMail);
                        }

                        // add teacher info
                        if(($acronym = trim($Document->getValue($Document->getCell($Location['LehrerKZ'], $RunY))))){
                            Teacher::useService()->insertTeacher($tblPerson, $acronym);
                        }
                    }

                    if(empty($error)){
                        $error = new SuccessText('Keine');
                    }

                    return new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Success('Es wurden '.$countStaff.' Mitarbeiter/Lehrer erfolgreich angelegt.', null, false, '2', '5')
                                , 6),
                            new LayoutColumn(
                                new Success($countStaffExists.' Mitarbeiter/Lehrer davon existierten bereits als Person.', null, false, '2', '5')
                                , 6),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                )
                            ),
                        ))
                    )));

                } else {
                    $MissingColumn = array();
                    foreach($Location as $Key => $Column){
                        if($Column === null){
                            $MissingColumn[] = $Key.': '.new DangerText('Spalte nicht gefunden!');
                        }
                    }
                    return new Warning(new Listing($MissingColumn)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $firstName
     * @param string $secondName
     * @param string $callName
     * @param string $lastName
     * @param string $birthName
     * @param null|bool $isProspect
     * @param string $ImportId
     *
     * @return bool|TblPerson
     */
    private function setPersonStudent(string $firstName, string $secondName, string $callName, string $lastName, string $birthName, string $studentGender,
        bool $isProspect = false, string $ImportId = ''): bool|TblPerson
    {

        $GroupList = array();
        if($isProspect === true) {
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        } elseif($isProspect === null) {
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
            $GroupList[] = Group::useService()->createGroupFromImport('Interessenten abgesagt');
        } else {
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        }

        $Salutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_STUDENT);
        if($studentGender){
            $studentGender = strtolower($studentGender);
            if(strlen($studentGender) > 1){
                $studentGender = substr($studentGender, 0, 1);
            }
            if($studentGender == 'w'
            || $studentGender === 1) {
                $Salutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_STUDENT_FEMALE);
            }
        }

        return Person::useService()->insertPerson(
            $Salutation,
            '',
            $firstName,
            $secondName,
            $lastName,
            $GroupList,
            $birthName,
            $ImportId,
            $callName
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Group
     *
     * @return void
     */
    private function setPersonGroup(TblPerson $tblPerson, string $Group): void
    {

        $tblGroup = Group::useService()->insertGroup($Group);
        Group::useService()->addGroupPerson($tblGroup, $tblPerson);
    }

    /**
     * @param string $salutation
     * @param string $title
     * @param string $firstName
     * @param string $lastName
     * @param string $memberNumber
     * @param string $assistance
     * @param string $contactNumber
     * @return bool|TblPerson
     */
    private function setPersonCustody(string $salutation, string $title, string $firstName, string $lastName, string $memberNumber = '', string $assistance = '',
        string $contactNumber = ''): bool|TblPerson
    {

        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if($memberNumber){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB);
        }

        $tblSalutation = false;
        $tblCommonGender = false;
        if($salutation){
            $salutation = strtolower($salutation);
            switch($salutation){
                case 'herr':
                case 'm':
                case 'h':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_MAN);
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Männlich');
                break;
                case 'frau':
                case 'w':
                case 'f':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_WOMAN);
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich');
                break;
                case 'd':
                case 'divers':
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Divers');
                break;
            }
        }

        $tblPerson = Person::useService()->insertPerson(
            $tblSalutation,
            $title,
            $firstName,
            '',
            $lastName,
            $GroupList
        );

        $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_NULL;
        if($assistance){
            $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_YES;
        }

        Common::useService()->insertMeta(
            $tblPerson,
            '',
            '',
            $tblCommonGender ? $tblCommonGender : null,
            '',
            '',
            $isAssistance,
            $assistance,
            '',
            $contactNumber
        );

        Club::useService()->insertMeta($tblPerson, $memberNumber);

        return $tblPerson;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $birthdayString
     * @param string    $birthPlace
     * @param string    $gender
     * @param string    $nationality
     * @param string    $denomination
     * @param string    $remark
     * @param int       $RunY
     * @param array     $error
     *
     * @return void
     */
    private function setPersonBirth(TblPerson $tblPerson, string $birthdayString, string $birthPlace, string $gender, string $nationality, string $denomination,
        string$remark, int $RunY, array &$error): void
    {
        // controll conform DateTime string
        $tblCommonGender = false;
        $birthday = $this->checkDate($birthdayString, 'Ungültiges Geburtsdatum:', $RunY, $error);
        if($gender != ''){
            $gender = strtolower($gender);
            switch ($gender){
                case 'm':
                case 'männlich':
                case 'mann':
                case '0':
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Männlich');
                break;
                case 'w':
                case 'weiblich':
                case 'frau':
                case '1':
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich');
                break;
                case 'd':
                case 'divers':
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Divers');
                break;
                case 'o':
                case 'ohne angabe':
                    $tblCommonGender = Common::useService()->getCommonGenderByName('Ohne Angabe');
                    break;
            }
        }

        Common::useService()->insertMeta(
            $tblPerson,
            $birthday,
            $birthPlace,
            $tblCommonGender ? $tblCommonGender : null,
            $nationality,
            $denomination,
            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
            '',
            $remark
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $remark
     *
     * @return void
     */
    private function setUpdateCommonRemark(TblPerson $tblPerson, string $remark = ''): void
    {
        if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
            if($remark){
                $remark = ($tblCommon->getRemark() ? $tblCommon->getRemark().'<br/>' : '').$remark;
                Common::useService()->insertUpdateCommon($tblCommon, $remark);
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $YearString
     * @param string    $divisionString
     * @param string    $level
     *
     * @return void
     */
    private function setPersonDivision(TblPerson $tblPerson, string $YearString, string $divisionString , string $level): void
    {

        $year = (int)$YearString;
        $yearShort = (int)substr($YearString, 2, 2);

        if ($divisionString === '') {
            return;
        }
        $tblYear = Term::useService()->insertYear($year.'/'.($yearShort + 1));
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodListByYear($tblYear);
            if (!$tblPeriodList) {
                // firstTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '1. Halbjahr',
                    '01.08.'.$year,
                    '31.01.'.($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }

                // secondTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '2. Halbjahr',
                    '01.02.'.($year + 1),
                    '31.07.'.($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }
            }

            $tblDivisionCourseDivision = $tblDivisionCourseCore = null;
            if($divisionString){
                $tblDivisionCourseTypeD = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_DIVISION);
                $tblDivisionCourseDivision = DivisionCourse::useService()->insertDivisionCourse($tblDivisionCourseTypeD, $tblYear, $divisionString);
            }
//            if($coreGroupString){
//                $tblDivisionCourseTypeC = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_CORE_GROUP);
//                $tblDivisionCourseCore = DivisionCourse::useService()->insertDivisionCourse($tblDivisionCourseTypeC, $tblYear, $coreGroupString);
//            }

//            $schoolType = strtolower($schoolType);
//            switch($schoolType){
//                case 'kinderhaus':
//                case 'kindertageseinrichtung':
//                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_KINDER_TAGES_EINRICHTUNG);
//                break;
//                case 'gs':
//                case 'grundschule':
//                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GRUND_SCHULE);
//                break;
//                case 'ms':
//                case 'os':
//                case 'mittelschule':
//                case 'oberschule':
//                case 'mittelschule/oberschule':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE);
//                break;
//                case 'wrs':
//                case 'werkrealschule':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_WERK_REAL_SCHULE);
//                break;
//                case 'gym':
//                case 'gymnasium':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
//                break;
//                case 'bs':
//                case 'berufsschule':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_SCHULE);
//                break;
//                case 'bfs':
//                case 'berufsfachschule':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_FACH_SCHULE);
//                break;
//                case 'bgy':
//                case 'berufliches gymnasium':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFLICHES_GYMNASIUM);
//                break;
//                case 'fos':
//                case 'fachoberschule':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_FACH_OBER_SCHULE);
//                break;
//                case 'bvj':
//                case 'berufsvorbereitungsjahr':
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_VORBEREITUNGS_JAHR);
//                break;
//                case 'iss';
//                case 'iss sek i gt';
//                case 'iss sek i';
//                case 'iss sek ii gt';
//                case 'iss sek ii';
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_INTEGRIERTE_SEKUNDAR_SCHULE);
//                break;
//                case 'gms';
//                case 'gemeinschaftsschule';
//                $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GEMEINSCHAFTS_SCHULE);
//                break;
//                default:
//                    $tblSchoolType = null;
//            }
//            if(!($tblCompany = Company::useService()->getCompanyByName($school, ''))) {
//                $tblCompany = null;
//            }

            DivisionCourse::useService()->insertStudentEducation($tblPerson, (int)$level, $tblYear, $tblDivisionCourseDivision, null, null, null, null);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $streetName
     * @param string    $streetNumber
     * @param string    $city
     * @param string    $cityCode
     * @param string    $district
     * @param string    $country
     * @param string    $nation
     * @param int       $RunY
     * @param array     $error
     *
     * @return void
     */
    private function setPersonAddress(TblPerson $tblPerson, string $streetName, string $streetNumber, string $city, string $cityCode, string $district,
        string $country, string $nation, int $RunY, array &$error): void
    {

        if($district == ''){
            $cityTemp = $city;
            if (preg_match('!(\w*\s)(OT\s\w*)!is', $cityTemp, $found)) {
                $city = $found[1];
                $district = $found[2];
            }
        }

        if($streetNumber == ''){
            $street = $streetName;
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }

        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $city
        ) {
            if(!($tblState = Address::useService()->getStateByName($country))){
                $tblState = null;
                if($country == 'BW'){
                    $tblState = Address::useService()->getStateByName('Baden-Württemberg');
                    $country = '';
                }
            }

                Address::useService()->insertAddressToPerson(
                    $tblPerson, $streetName, $streetNumber, $cityCode, $city,
                    $district, '', $country, $nation, $tblState
                );
        } else {
            $error[] = new DangerText('Zeile: '.($RunY + 1)).' '.$tblPerson->getLastFirstName().' Adresse konnte nicht angelegt werden.';
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param array    $emergencyPhone [Number, IsMobile, Remark]
     *
     * @return void
     */
    private function setPersonPhone(TblPerson $tblPerson, array $PhoneList): void
    {

        $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_PHONE);
        $tblTypeMobile = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
        foreach ($PhoneList as $Phone){
            if(!$Phone['IsMobile']) {
                Phone::useService()->insertPhoneToPerson($tblPerson, $Phone['Number'], $tblType, $Phone['Remark']);
            } else {
                Phone::useService()->insertPhoneToPerson($tblPerson, $Phone['Number'], $tblTypeMobile, $Phone['Remark']);
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Number
     * @param string $Type
     * @param string $PhoneType
     *
     * @return void
     */
    private function setPersonPhoneWithType(TblPerson $tblPerson, string $Number,
        string $Type = TblTypePhone::VALUE_NAME_PRIVATE,
        string $PhoneType = TblTypePhone::VALUE_DESCRIPTION_PHONE): void
    {

        $tblType = Phone::useService()->getTypeByNameAndDescription($Type, $PhoneType);
        if($tblType){
            Phone::useService()->insertPhoneToPerson($tblPerson, $Number, $tblType, '');
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $privateMail
     * @return void
     */
    private function setPersonMail(TblPerson $tblPerson, string $privateMail): void
    {

        if($privateMail){
            $tblType = Mail::useService()->getTypeByName(TblTypeMail::VALUE_PRIVATE);
            Mail::useService()->insertMailToPerson($tblPerson, $privateMail, $tblType, '');
        }
    }

    /**
     * @param TblPerson       $tblPerson
     * @param string          $Identification
     * @param string          $schoolAttendanceStartDate
     * @param string          $arriveDate
     * @param string          $arriveRemark
     * @param string          $disease
     * @param string          $medication
     * @param string          $insurance
     * @param string          $religion
     * @param string          $enrollmentDate
     * @param null|TblCompany $tblCompanyStammschule
     * @param string          $schoolEnrollmentType
     * @param string          $specialNeedsLevel
     * @param int             $RunY
     * @param array           $error
     *
     * @return void
     */
    private function setPersonTblStudent(TblPerson $tblPerson, string $Identification, string $schoolAttendanceStartDate, string $arriveDate,
        string $arriveRemark, string $disease, string $medication, string $insurance, string $religion, string $enrollmentDate,
        ?TblCompany $tblCompanyStammschule, string $schoolEnrollmentType, string $specialNeedsLevel, string $ForeignLanguage1, string $ForeignLanguage2,
        string $hasMigrationBackground, string $MigrationBackground, int $RunY, array &$error)
    {
        // controll conform DateTime string
        $schoolAttendanceStartDate = $this->checkDate($schoolAttendanceStartDate, 'Ungültiges Schulpflichtbeginn-Datum:', $RunY, $error);

        $tblStudentMedicalRecord = null;
        if($disease != '' || $medication != '' || $insurance != ''){
            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord($disease, $medication, $insurance);
        }
        $tblStudentSpecialNeeds = null;
        if($specialNeedsLevel){
            $tblStudentSpecialNeeds = Student::useService()->insertStudentSpecialNeedsLevel($specialNeedsLevel);
        }
        // Student
        $tblStudent = Student::useService()->insertStudent($tblPerson, $Identification, $tblStudentMedicalRecord, null, null, null, null,
            $tblStudentSpecialNeeds, $schoolAttendanceStartDate, $hasMigrationBackground, $MigrationBackground);

        if($religion){
            if($religion == 'EV'){
                $religion = 'RE/e';
            }
            $tblSubject = Subject::useService()->getSubjectByAcronym($religion);
            if(!$tblSubject){
                $tblSubject = Subject::useService()->getSubjectByName($religion);
            }
            if($tblSubject){
                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_RELIGION);
                $tblSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                Student::useService()->addStudentSubject($tblStudent, $tblStudentSubjectType,$tblSubjectRanking, $tblSubject);
            }
        }
        // ForeignLanguage1
        if($ForeignLanguage1){
            $tblSubject = false;
            $ForeignLanguage1 = $this->replaceSubjectForeign($ForeignLanguage1);
            if(!is_numeric($ForeignLanguage1)
            && !($tblSubject = Subject::useService()->getSubjectByName($ForeignLanguage1))){
                $tblSubject = Subject::useService()->insertSubject(strtoupper(substr($ForeignLanguage1, 0, 3)), $ForeignLanguage1);
                $tblCategory = Subject::useService()->getCategoryByIdentifier(TblCategory::IDENTIFIER_FOREIGN_LANGUAGE);
                Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
            }
            if($tblSubject){
                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_FOREIGN_LANGUAGE);
                $tblSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                Student::useService()->addStudentSubject($tblStudent, $tblStudentSubjectType,$tblSubjectRanking, $tblSubject);
            }
        }
        // ForeignLanguage2
        if($ForeignLanguage2){
            $tblSubject = false;
            $ForeignLanguage2 = $this->replaceSubjectForeign($ForeignLanguage2);
            if(!is_numeric($ForeignLanguage2)
                && !($tblSubject = Subject::useService()->getSubjectByName($ForeignLanguage2))){
                $tblSubject = Subject::useService()->insertSubject(strtoupper(substr($ForeignLanguage2, 0, 3)), $ForeignLanguage2);
                $tblCategory = Subject::useService()->getCategoryByIdentifier(TblCategory::IDENTIFIER_FOREIGN_LANGUAGE);
                Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
            }
            if($tblSubject){
                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_FOREIGN_LANGUAGE);
                $tblSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('2');
                Student::useService()->addStudentSubject($tblStudent, $tblStudentSubjectType,$tblSubjectRanking, $tblSubject);
            }
        }

        if ($enrollmentDate
        && ($enrollmentDate = $this->checkDate($enrollmentDate, 'Ungültiges Einschulungsdatum:', $RunY, $error))
        && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::ENROLLMENT))) {
            Student::useService()->insertStudentTransfer($tblStudent, $tblStudentTransferType, null, null, null, $enrollmentDate);
        }
        if($arriveDate || $schoolEnrollmentType) {
            if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::ARRIVE))) {
                $arriveDate = $this->checkDate($arriveDate, 'Ungültiges Aufnahme-Datum:', $RunY, $error);
            }
            if(!($tblStudentSchoolEnrollmentType = Student::useService()->getStudentSchoolEnrollmentTypeByName($schoolEnrollmentType))){
                $tblStudentSchoolEnrollmentType = null;
            }
            Student::useService()->insertStudentTransfer($tblStudent, $tblStudentTransferType, null,
                null, null, $arriveDate, $arriveRemark, $tblCompanyStammschule, $tblStudentSchoolEnrollmentType);
        }
    }

    /**
     * @param string $Date
     * @param string $ErrorMessage
     * @param int $RunY
     * @param array $error
     * 
     * @return string
     */
    private function checkDate(string $Date, string $ErrorMessage, int $RunY, array &$error): string
    {

        $result = '';
        if ($Date !== '') {
            $len = strlen($Date);
            switch ($len) {
                case 5:
                    $result = date('d.m.Y', Date::excelToTimestamp($Date));
                    break;
                case 6:
                    $result = substr($Date, 0, 2).'.'.substr($Date, 2, 2).'.'.substr($Date, 4, 2);
                    break;
                case 7:
                    $Date = '0'.$Date;
                case 8:
                    $result = substr($Date, 0, 2).'.'.substr($Date, 2, 2).'.'.substr($Date, 4, 4);
                    break;
                case 10:
                    $result = $Date;
                    break;
                default:
                    $error[] = new DangerText('Zeile: '.($RunY + 1)).' '.$ErrorMessage.'Datumsformat nicht erkannt';
            }
        }
        return $result;
    }

    /**
     * @param string $salutation
     * @param string $titel
     * @param string $firstName
     * @param string $lastName
     *
     * @return TblPerson
     */
    private function setPersonStaff(string $salutation, string $titel, string $firstName, string $lastName, string $BirthName): tblPerson
    {

        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);

        $tblSalutation = false;
        if($salutation){
            $salutation = strtolower($salutation);
            switch($salutation){
                case 'herr':
                case 'm':
                case 'h':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_MAN);
                    break;
                case 'frau':
                case 'w':
                case 'f':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_WOMAN);
                    break;
            }
        }

        return Person::useService()->insertPerson(
            $tblSalutation,
            $titel,
            $firstName,
            '',
            $lastName,
            $GroupList,
            $BirthName
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @return void
     */
    private function setGroupStaff(TblPerson $tblPerson): void
    {

        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        // Add to Group
        if (!empty( $GroupList )) {
            foreach ($GroupList as $tblGroup) {
                Group::useService()->addGroupPerson($tblGroup, $tblPerson);
            }
        }
    }

    /**
     * Zerlegt einen rohen Telefon-/Fax-Feldwert in Nummer, Typ (Mobil/Festnetz) und Bemerkung.
     *
     * Beispiele:
     *   "07126/123456"        => Number "07126/123456", IsMobile false, Remark ""
     *   "0157 12345678 (M)"   => Number "0157 12345678", IsMobile true,  Remark "(M)"
     *   "0178-1234567 (M)"    => Number "0178-1234567",  IsMobile true,  Remark "(M)"
     *
     * @param string $rawValue roher Feldinhalt, z.B. $this->getValue('TelefaxBezPers1')
     *
     * @return false|array  array('Number' => string, 'IsMobile' => bool, 'Remark' => string)
     *                       oder false, wenn kein Wert vorhanden ist
     */
    private function parsePhoneField(string $rawValue): bool|array
    {
        $rawValue = trim((string)$rawValue);
        if ($rawValue === '') {
            return false;
        }
//        // Nummernteil = führende Folge aus Ziffern/Trennzeichen (+ / - und Leerzeichen),
//        // alles dahinter ist Bemerkung (z.B. "(M)", "geschäftlich", eine zweite Nummer ...)
//        if (!preg_match('/^([+0-9][0-9\s\/\-]*)(.*)$/', $rawValue, $match)) {
//            // beginnt nicht mit einer Nummer -> nur als Bemerkung behalten, keine Nummer
//            return array('Number' => '', 'IsMobile' => false, 'Remark' => $rawValue);
//        }

        preg_match('/^([+0-9][0-9\s\/\-]*)(.*)$/', $rawValue, $match);

        $number = trim($match[1] ?? '');
        $remark = trim($match[2] ?? '');

        // Vergleichsform: nur Ziffern, +49/0049 auf 0 normalisieren
        $digits = preg_replace('/\D/', '', $number);
        $digits = preg_replace('/^(0049|49)/', '0', $digits);

        // Mobil, wenn (a) explizit gekennzeichnet ("(M)", "mobil") ODER
        //             (b) deutsche Mobilvorwahl 015x / 016x / 017x
        $isMobile = (bool)(
            preg_match('/\(?\bm(obil)?\b\.?\)?/i', $remark)
            || preg_match('/^01[567]/', $digits)
        );

        return array(
            'Number'   => $number,
            'IsMobile' => $isMobile,
            'Remark'   => $remark,
        );
    }

    /**
     * @param string $Nationality
     *
     * @return string
     */
    private function replaceNationality(string $Nationality): string
    {

        $mapping = $this->Nation;
        $key = trim($Nationality);
        if (isset($mapping[$key])) {
            return $mapping[$key];
        }

        return $Nationality;
    }

    /**
     * @param string $Subject
     *
     * @return string
     */
    private function replaceSubjectForeign(string $Subject): string
    {
        $mapping = $this->Subject;
        $key = trim($Subject);
        if (isset($mapping[$key])) {
            return $mapping[$key];
        }

        return $Subject;
    }

    /**
     * @param string $Religion
     *
     * @return string
     */
    private function replaceSubjectReligion(string $Religion): string
    {
        $mapping = $this->Religion;
        $key = trim($Religion);
        if (isset($mapping[$key])) {
            return $mapping[$key];
        }

        return $Religion;
    }

    /**
     * Trennt eine Adresse in Straße und Hausnummer.
     *
     * Unterstützt Formate wie:
     *  - "18/1"
     *  - "9-1"
     *  - "8"
     *  - "12a"
     *  - "12 a"
     *
     * @param string $adresse
     * @return array{strasse: string, hausnummer: ?string}
     */
    function splitStreetAndNumber(string $adresse): array
    {
        $adresse = trim($adresse);

        // Lazy-Match: nimmt so wenig wie möglich als "Straße",
        // bis der Rest komplett als Hausnummer-Muster passt.
        $pattern = '/^(.+?)\s+(\d+[a-zA-Z]?(?:[\/\-]\d+[a-zA-Z]?)?)$/u';

        if (preg_match($pattern, $adresse, $matches)) {
            return [
                'street'    => trim($matches[1]),
                'number' => trim($matches[2]),
            ];
        }

        // Kein Match -> keine Hausnummer gefunden
        return [
            'street'    => $adresse,
            'number' => '',
        ];
    }
}