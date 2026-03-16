<?php
namespace SPHERE\Application\Contact\Address\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblCountry;
use SPHERE\Application\Contact\Address\Service\Entity\TblRegion;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\RedisHandler;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\IdHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\Contact\Address\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

        if(!$this->getTypeByName('Hauptadresse')){
            $this->createType('Hauptadresse');
            $this->createType('Zweit-/Nebenadresse');
            $this->createType('Rechnungsadresse');
            $this->createType('Lieferadresse');
        }

        // StateByName faster than StateAll
        if(!$this->getStateByName('Sachsen')){
            $this->createState('Baden-Württemberg');
            $this->createState('Bremen');
            $this->createState('Niedersachsen');
            $this->createState('Sachsen');
            $this->createState('Bayern');
            $this->createState('Hamburg');
            $this->createState('Nordrhein-Westfalen');
            $this->createState('Sachsen-Anhalt');
            $this->createState('Berlin');
            $this->createState('Hessen');
            $this->createState('Rheinland-Pfalz');
            $this->createState('Schleswig-Holstein');
            $this->createState('Brandenburg');
            $this->createState('Mecklenburg-Vorpommern');
            $this->createState('Saarland');
            $this->createState('Thüringen');
        }

        // new Region? set if to new Region to install by already installed Regions
        if(!$this->getRegionListByName('Mitte')){
            $this->createRegion('Mitte', array(
                '10115', '10117', '10119', '10178', '10179', '10435', '10551', '10553', '10555', '10557', '10559', '10623',
                '10785', '10787', '10963', '10969', '13347', '13349', '13351', '13353', '13355', '13357', '13359', '13405',
                '13407', '13409'
            ));
            $this->createRegion('Friedrichshain-Kreuzberg', array(
                '10179', '10243', '10245', '10247', '10249', '10367', '10785', '10961', '10963', '10965', '10967', '10969',
                '10997', '10999', '12045', '10178'
            ));
            $this->createRegion('Pankow', array(
                '10119', '10247', '10249', '10405', '10407', '10409', '10435', '10437', '10439', '13051', '13053', '13086',
                '13088', '13089', '13125', '13127', '13129', '13156', '13158', '13159', '13187', '13189'
            ));
            $this->createRegion('Charlottenburg-Wilmersdorf', array(
                '10553', '10585', '10587', '10589', '10623', '10625', '10627', '10629', '10707', '10709', '10711', '10713',
                '10715', '10717', '10719', '10777', '10779', '10787', '10789', '10825', '13353', '13597', '13627', '13629',
                '14050', '14052', '14053', '14055', '14057', '14059', '14193', '14195', '14197', '14199'
            ));
            $this->createRegion('Spandau', array(
                '13581', '13583', '13585', '13587', '13589', '13591', '13593', '13595', '13597', '13599', '13627', '13629',
                '14052', '14089'
            ));
            $this->createRegion('Steglitz-Zehlendorf', array(
                '12157', '12161', '12163', '12165', '12167', '12169', '12203', '12205', '12207', '12209', '12247', '12249',
                '12277', '12279', '14109', '14129', '14163', '14165', '14167', '14169', '14193', '14195', '14197', '14199'
            ));
            $this->createRegion('Tempelhof-Schöneberg', array(
                '10777', '10779', '10781', '10783', '10785', '10787', '10789', '10823', '10825', '10827', '10829', '10965',
                '12099', '12101', '12103', '12105', '12107', '12109', '12157', '12159', '12161', '12163', '12169', '12249',
                '12277', '12279', '12305', '12307', '12309', '12347', '14197'
            ));
            $this->createRegion('Neukölln', array(
                '10965', '10967', '12043', '12045', '12047', '12049', '12051', '12053', '12055', '12057', '12059', '12099',
                '12107', '12305', '12347', '12349', '12351', '12353', '12355', '12357', '12359'
            ));
            $this->createRegion('Treptow-Köpenick', array(
                '12435', '12437', '12439', '12459', '12487', '12489', '12524', '12526', '12527', '12555', '12557', '12559',
                '12587', '12589', '12623'
            ));
            $this->createRegion('Marzahn-Hellersdorf', array(
                '12555', '12619', '12621', '12623', '12627', '12629', '12679', '12681', '12683', '12685', '12687', '12689'
            ));
            $this->createRegion('Lichtenberg', array(
                '10315', '10317', '10318', '10319', '10365', '10367', '10369', '13051', '13053', '13055', '13057', '13059'
            ));
            $this->createRegion('Reinickendorf', array(
                '13403', '13405', '13407', '13409', '13435', '13437', '13439', '13465', '13467', '13469', '13503', '13505',
                '13507', '13509', '13599', '13629'
            ));
        }
        if(!$this->getCountryByName('Deutschland')){
            $CountryList = array(
                "000" => "Deutschland",
                "112" => "Gibraltar (Britisches Überseegebiet)",
                "113" => "Guernsey (Britisches Überseegebiet)",
                "114" => "Jersey (Britisches Überseegebiet)",
                "115" => "Insel Man (Britisches Überseegebiet)",
                "116" => "Svalbard und Jan Mayen (u. a. Bäreninsel, Spitzbergen) (Norwegisches Überseegebiet)",
                "121" => "Albanien",
                "122" => "Bosnien und Herzegowina",
                "123" => "Andorra",
                "124" => "Belgien",
                "125" => "Bulgarien",
                "126" => "Dänemark",
                "127" => "Estland",
                "128" => "Finnland",
                "129" => "Frankreich, einschl. Korsika",
                "130" => "Kroatien",
                "131" => "Slowenien",
                "134" => "Griechenland",
                "135" => "Irland",
                "136" => "Island",
                "137" => "Italien",
                "139" => "Lettland",
                "140" => "Montenegro",
                "141" => "Liechtenstein",
                "142" => "Litauen",
                "143" => "Luxemburg",
                "144" => "Nordmazedonien",
                "145" => "Malta",
                "146" => "Republik Moldau (Moldawien)",
                "147" => "Monaco",
                "148" => "Niederlande",
                "149" => "Norwegen",
                "150" => "Kosovo",
                "151" => "Österreich",
                "152" => "Polen",
                "153" => "Portugal",
                "154" => "Rumänien",
                "155" => "Slowakei",
                "156" => "San Marino",
                "157" => "Schweden",
                "158" => "Schweiz",
                "160" => "Russland",
                "161" => "Spanien",
                "163" => "Türkei",
                "164" => "Tschechien",
                "165" => "Ungarn",
                "166" => "Ukraine",
                "167" => "Vatikanstadt",
                "168" => "Vereinigtes Königreich (Großbritannien und Nordirland)",
                "169" => "Belarus",
                "170" => "Serbien",
                "181" => "Zypern",
                "182" => "Färöer (Dänisches Überseegebiet)",
                "185" => "Britisches Überseegebiet außerhalb Europas",
                "211" => "Mayotte (Französisches Überseegebiet)",
                "214" => "Réunion (Französisches Überseegebiet)",
                "216" => "Spanische Hoheitsplätze in Nordafrika (Spanisches Überseegebiet)",
                "221" => "Algerien",
                "223" => "Angola",
                "224" => "Eritrea",
                "225" => "Äthiopien",
                "226" => "Lesotho",
                "227" => "Botsuana",
                "229" => "Benin",
                "230" => "Dschibuti",
                "231" => "Côte d`Ivoire",
                "232" => "Nigeria",
                "233" => "Simbabwe",
                "236" => "Gabun",
                "237" => "Gambia",
                "238" => "Ghana",
                "239" => "Mauretanien",
                "242" => "Cabo Verde",
                "243" => "Kenia",
                "244" => "Komoren",
                "245" => "Kongo, Republik",
                "246" => "Kongo, Dem. Republik",
                "247" => "Liberia",
                "248" => "Libyen",
                "249" => "Madagaskar",
                "251" => "Mali",
                "252" => "Marokko",
                "253" => "Mauritius",
                "254" => "Mosambik",
                "255" => "Niger",
                "256" => "Malawi",
                "257" => "Sambia",
                "258" => "Burkina Faso",
                "259" => "Guinea-Bissau",
                "261" => "Guinea",
                "262" => "Kamerun",
                "263" => "Südafrika",
                "265" => "Ruanda",
                "267" => "Namibia",
                "268" => "São Tomé und Príncipe",
                "269" => "Senegal",
                "271" => "Seychellen",
                "272" => "Sierra Leone",
                "273" => "Somalia",
                "274" => "Äquatorialguinea",
                "277" => "Sudan",
                "278" => "Südsudan",
                "281" => "Eswatini (ehem. Swasiland)",
                "282" => "Vereinigte Republik Tansania",
                "283" => "Togo",
                "284" => "Tschad",
                "285" => "Tunesien",
                "286" => "Uganda",
                "287" => "Ägypten",
                "289" => "Zentralafrikanische Republik",
                "291" => "Burundi",
                "311" => "Aruba (Niederländisches Überseegebiet)",
                "315" => "Französisch-Guayana (Französisches Überseegebiet)",
                "316" => "Amerikanische Jungferninseln (US-Überseegebiet)",
                "317" => "Guadeloupe (Französisches Überseegebiet)",
                "319" => "Martinique (Französisches Überseegebiet)",
                "320" => "Antigua und Barbuda",
                "321" => "Curaçao (Niederländisches Überseegebiet)",
                "322" => "Barbados",
                "323" => "Argentinien",
                "324" => "Bahamas",
                "325" => "Puerto Rico (US-Überseegebiet)",
                "326" => "Bolivien",
                "327" => "Brasilien",
                "328" => "Guyana",
                "329" => "St. Barthélemy (Französisches Überseegebiet)",
                "330" => "Belize",
                "331" => "St. Martin (französischer Teil) (Französisches Überseegebiet)",
                "332" => "Chile",
                "333" => "Dominica",
                "334" => "Costa Rica",
                "335" => "Dominikanische Republik",
                "336" => "Ecuador, einschl. Galapagos-Inseln",
                "337" => "El Salvador",
                "338" => "St. Pierre und Miquelon (Französisches Überseegebiet)",
                "340" => "Grenada",
                "341" => "St. Martin (niederländischer Teil) (Niederländisches Überseegebiet)",
                "342" => "Grönland (Dänisches Überseegebiet)",
                "343" => "Navassa (US-Überseegebiet)",
                "344" => "Bonaire, Saba, St. Eustatius (Niederländisches Überseegebiet)",
                "345" => "Guatemala",
                "346" => "Haiti",
                "347" => "Honduras",
                "348" => "Kanada",
                "349" => "Kolumbien",
                "351" => "Kuba",
                "352" => "Clipperton (Französisches Überseegebiet)",
                "353" => "Mexiko",
                "354" => "Nicaragua",
                "355" => "Jamaika",
                "357" => "Panama",
                "359" => "Paraguay",
                "361" => "Peru",
                "364" => "Suriname",
                "365" => "Uruguay",
                "366" => "St. Lucia",
                "367" => "Venezuela",
                "368" => "Vereinigte Staaten (von Amerika), auch USA",
                "369" => "St. Vincent und die Grenadinen",
                "370" => "St. Kitts und Nevis",
                "371" => "Trinidad und Tobago",
                "411" => "Hongkong",
                "412" => "Macau",
                "421" => "Jemen",
                "422" => "Armenien",
                "423" => "Afghanistan",
                "424" => "Bahrain",
                "425" => "Aserbaidschan",
                "426" => "Bhutan",
                "427" => "Myanmar",
                "429" => "Brunei Darussalam",
                "430" => "Georgien",
                "431" => "Sri Lanka",
                "432" => "Vietnam",
                "434" => "Dem. Volksrepublik Korea",
                "436" => "Indien, einschl. Sikkim und Gôa",
                "437" => "Indonesien, einschl. Irian Jaya",
                "438" => "Irak",
                "439" => "Iran, Islamische Republik",
                "441" => "Israel",
                "442" => "Japan",
                "444" => "Kasachstan",
                "445" => "Jordanien",
                "446" => "Kambodscha",
                "447" => "Katar",
                "448" => "Kuwait",
                "449" => "Dem. Volksrepublik Laos",
                "450" => "Kirgisistan",
                "451" => "Libanon",
                "454" => "Malediven",
                "456" => "Oman",
                "457" => "Mongolei",
                "458" => "Nepal",
                "459" => "Palästinensische Gebiete",
                "460" => "Bangladesch",
                "461" => "Pakistan",
                "462" => "Philippinen",
                "465" => "Taiwan",
                "467" => "Republik Korea, auch Süd-Korea",
                "469" => "Vereinigte Arabische Emirate",
                "470" => "Tadschikistan",
                "471" => "Turkmenistan",
                "472" => "Saudi-Arabien",
                "474" => "Singapur",
                "475" => "Arabische Republik Syrien",
                "476" => "Thailand",
                "477" => "Usbekistan",
                "479" => "China",
                "482" => "Malaysia",
                "483" => "Timor-Leste",
                "499" => "Übriges Asien",
                "510" => "Heard und McDonaldinseln (Australisches Überseegebiet)",
                "511" => "Korallenmeerinseln (Australisches Überseegebiet)",
                "512" => "Kokosinseln (Australisches Überseegebiet)",
                "513" => "Neukaledonien (Französisches Überseegebiet)",
                "514" => "Nördliche Marianen (US-Überseegebiet)",
                "515" => "Norfolkinsel (Australisches Überseegebiet)",
                "517" => "Amerikanisch-Samoa (US-Überseegebiet)",
                "519" => "Tokelau (Neuseeländisches Überseegebiet)",
                "520" => "Wallis und Futuna (Französisches Überseegebiet)",
                "521" => "Weihnachtsinsel (Australisches Überseegebiet)",
                "522" => "Bouvetinsel (Norwegisches Überseegebiet)",
                "523" => "Australien",
                "524" => "Salomonen",
                "525" => "Ashmore- und Cartierinseln (Australisches Überseegebiet)",
                "526" => "Fidschi",
                "527" => "Cookinseln",
                "528" => "Französisch-Polynesien (Französisches Überseegebiet)",
                "529" => "Guam (US-Überseegebiet)",
                "530" => "Kiribati",
                "531" => "Nauru",
                "532" => "Vánúatú",
                "533" => "Niue",
                "534" => "Kleinere Amerikanische Überseeinseln (US-Überseegebiet)",
                "535" => "Norwegisches Antarktis-Territorium (Norwegisches Überseegebiet)",
                "536" => "Neuseeland",
                "537" => "Palau",
                "538" => "Papua-Neuguinea",
                "540" => "Tuvalu",
                "541" => "Tonga",
                "542" => "Französische Süd- und Antarktisgebiete (Französisches Überseegebiet)",
                "543" => "Samoa, auch Westsamoa",
                "544" => "Marschallinseln",
                "545" => "Föderierte Staaten von Mikronesien",
                "546" => "Antarktis, Chilenische (Chilenisches Überseegebiet)",
                "547" => "Australisches Antarktis-Territorium (Australisches Überseegebiet)",
                "548" => "Argentinische Antarktis (Argentinisches Überseegebiet)",
                "549" => "Neuseeländische Antarktis: Ross-Nebengebiet (Neuseeländisches Überseegebiet)",
                "996" => "Staatenlos",
                "997" => "Ungeklärt",
                "998" => "Keine Angabe"
            );
            $this->createCountryByList($CountryList);
        }
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblType
     */
    public function createType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblType')->findOneBy(array(
            TblType::ATTR_NAME        => $Name,
            TblType::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Name
     *
     * @return TblState
     */
    public function createState($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblState')->findOneBy(array(
            TblState::ATTR_NAME => $Name,
        ));
        if (null === $Entity) {
            $Entity = new TblState($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string       $Name
     * @param string|array $Code
     *
     * return void
     */
    public function createRegion(string $Name, $Code): void
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(is_array($Code)){
            foreach($Code as $Plz){
                $Entity = $Manager->getEntity('TblRegion')->findOneBy(array(
                    TblRegion::ATTR_NAME => $Name,
                    TblRegion::ATTR_CODE => $Plz,
                ));

                if (null === $Entity) {
                    $Entity = new TblRegion();
                    $Entity->setName($Name);
                    $Entity->setCode($Plz);

                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        } else {
            $Entity = $Manager->getEntity('TblRegion')->findOneBy(array(
                TblRegion::ATTR_NAME => $Name,
                TblRegion::ATTR_CODE => $Code,
            ));

            if (null === $Entity) {
                $Entity = new TblRegion();
                $Entity->setName($Name);
                $Entity->setCode($Code);
                $Manager->saveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            }
//            return $Entity;
        }
    }

    /**
     * @param array $CountryList // z.B. array("000" => "Deutschland")
     *
     * return void
     */
    public function createCountryByList(array $CountryList): void
    {

        $Manager = $this->getConnection()->getEntityManager();
        foreach($CountryList as $Extern => $Name){
            $Entity = $Manager->getEntity('TblCountry')->findOneBy(array(
                TblCountry::ATTR_NAME => $Name,
                TblCountry::ATTR_EXTERN => $Extern,
            ));
            if (null === $Entity) {
                $Entity = new TblCountry();
                $Entity->setName($Name);
                $Entity->setExtern($Extern);
                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblState
     */
    public function getStateByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState', array(
            TblType::ATTR_NAME => $Name,
        ));
    }

    /**
     * @param string $Region
     *
     * @return bool|TblRegion[]
     */
    public function getRegionListByName($Name)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblRegion', array(
            TblRegion::ATTR_NAME => $Name,
        ));
    }
    /**
     * @param string $Region
     *
     * @return bool|TblRegion[]
     */
    public function getRegionListByCode($Code)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblRegion', array(
            TblRegion::ATTR_CODE => $Code,
        ));
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCity
     */
    public function getCityById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCity', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCountry
     */
    public function getCountryById($Id): bool|TblCountry
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCountry', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAddress
     */
    public function getAddressById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddress', $Id);
    }

    /**
     * @return bool|TblCity[]
     */
    public function getCityAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCity');
    }

    /**
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState');
    }

    /**
     * @return bool|TblCountry[]
     */
    public function getCountryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCountry');
    }

    /**
     * @return bool|TblRegion[]
     */
    public function getRegionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblRegion');
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType');
    }

    /**
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddress');
    }

    /**
     *  Array include:
     *  [Id], // TblAddress Id
     *  [StreetName],
     *  [StreetNumber],
     *  [PostOfficeBox],
     *  [County],
     *  [Nation],
     *  [Region],
     *  [AddressExtra],
     *  [Code],
     *  [CityName],
     *  [District]
     *  [StateName]
     *  [CountryName]
     * @return bool|TblAddress[]
     */
    public function getAddressNotLinked()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();
        $tblAddress = new TblAddress();
        $tblToPerson = new TblToPerson();
        $tblToCompany = new TblToCompany();
        $tblCity = new TblCity();
        $tblState = new TblState('');
        $tblCountry = new TblCountry();

        $Query = $Builder->select('tA.Id, tA.StreetName, tA.StreetNumber, tA.PostOfficeBox, tA.County, tA.Nation, tA.Region, tA.AddressExtra, tC.Code, tC.Name as CityName, tC.District, tS.Name as StateName, tCo.Name as CountryName')
            ->from($tblAddress->getEntityFullName(), 'tA')
            ->leftJoin($tblCity->getEntityFullName(), 'tC', 'WITH', 'tC.Id = tA.tblCity')
            ->leftJoin($tblState->getEntityFullName(), 'tS', 'WITH', 'tS.Id = tA.tblState')
            ->leftJoin($tblCountry->getEntityFullName(), 'tCo', 'WITH', 'tCo.Id = tA.tblCountry')
            ->leftJoin($tblToPerson->getEntityFullName(), 'tTP', 'WITH', 'tTP.tblAddress = tA.Id')
            ->leftJoin($tblToCompany->getEntityFullName(), 'tTC', 'WITH', 'tTC.tblAddress = tA.Id')
            ->where($Builder->expr()->isNull('tTP.Id'))
            ->andWhere($Builder->expr()->isNull('tTC.Id'))
            ->getQuery();

        $resultList = $Query->getResult();
        return $resultList;
    }

    /**
     * Array include:
     * [StreetName],
     * [County],
     * [Nation],
     * [Code],
     * [Name],
     * [District]
     * <br/> distinct & only with existing Usage
     * @return bool|TblAddress[]
     */
    public function getAddressAllForAutoCompleter()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();
        $tblToPerson = new TblToPerson();
        $tblAddress = new TblAddress();
        $tblCity = new TblCity();
        $Query = $Builder->select('tA.AddressExtra, tA.StreetName, tA.County, tC.Code, tC.Name, tC.District')
            ->from($tblToPerson->getEntityFullName(), 'tTP')
            ->leftJoin($tblAddress->getEntityFullName(), 'tA', 'WITH', 'tA.Id = tTP.tblAddress')
            ->leftJoin($tblCity->getEntityFullName(), 'tC', 'WITH', 'tC.Id = tA.tblCity')
            ->where($Builder->expr()->isNull('tTP.EntityRemove'))
            ->distinct()
            ->getQuery();

        $resultList = $Query->getResult();
        return $resultList;

    }

    /**
     * @param string $Name
     *
     * @return bool|TblCountry
     */
    public function getCountryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCountry',
            array(
                TblCountry::ATTR_NAME => $Name
            ));
    }

    /**
     * @param string $Extern
     *
     * @return bool|TblCountry
     */
    public function getCountryByExtern($Extern)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCountry',
            array(
                TblCountry::ATTR_EXTERN => $Extern
            ));
    }

    /**
     * @param string $Code
     * @param string $Name
     * @param string $District
     *
     * @return TblCity
     */
    public function createCity($Code, $Name, $District)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $tblCityList= $Manager->getEntity('TblCity')->findBy(array(
            TblCity::ATTR_CODE     => $Code,
            TblCity::ATTR_NAME     => $Name,
            TblCity::ATTR_DISTRICT => $District
        ));

        // SSW-533 Entity-Manager ignoriert die Groß- und Kleinschreibung
        /** @var TblCity $tblCity */
        if ($tblCityList) {
            foreach ($tblCityList as $tblCity) {
                if ($tblCity->getCode() == $Code
                    && $tblCity->getName() == $Name
                    && $tblCity->getDistrict() == $District
                ) {
                    return $tblCity;
                }
            }
        }

        $Entity = new TblCity();
        $Entity->setCode($Code);
        $Entity->setName($Name);
        $Entity->setDistrict($District);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param ?TblState   $tblState
     * @param TblCity     $tblCity
     * @param string      $StreetName
     * @param string      $StreetNumber
     * @param string      $PostOfficeBox
     * @param string      $Region
     * @param string      $County
     * @param string      $Nation // try to convert string into tblCountry if tblCountry is empty
     * @param string      $AddressExtra
     * @param ?TblCountry $tblCountry
     *
     * @return TblAddress
     */
    public function createAddress(
        TblState $tblState = null,
        TblCity $tblCity,
        string $StreetName,
        string $StreetNumber,
        string $PostOfficeBox,
        string $Region = '',
        string $County = '',
        string $Nation = '',
        string $AddressExtra = '',
        TblCountry $tblCountry = null
    ) {

        // Try to match Country by Nation
        if($Nation && $tblCountry === null){
            $tblCountry = $this->getCountryByName($Nation);
            if(!$tblCountry){
                $tblCountry = null;
            }
        }

        $Manager = $this->getConnection()->getEntityManager();
        $tblAddressList = $Manager->getEntity('TblAddress')
            ->findBy(array(
                TblAddress::ATTR_TBL_STATE       => ( $tblState ? $tblState->getId() : null ),
                TblAddress::ATTR_TBL_CITY        => $tblCity->getId(),
                TblAddress::ATTR_STREET_NAME     => $StreetName,
                TblAddress::ATTR_STREET_NUMBER   => $StreetNumber,
                TblAddress::ATTR_POST_OFFICE_BOX => $PostOfficeBox,
                TblAddress::ATTR_REGION          => $Region,
                TblAddress::ATTR_COUNTY          => $County,
                TblAddress::ATTR_NATION          => $Nation,
                TblAddress::ATTR_ADDRESS_EXTRA   => $AddressExtra,
                TblAddress::ATTR_TBL_COUNTRY     => $tblCountry,
            ));

        // SSW-533 Entity-Manager ignoriert die Groß- und Kleinschreibung
        /** @var TblAddress $tblAddress */
        if ($tblAddressList) {
            foreach ($tblAddressList as $tblAddress) {
                if ($tblAddress->getStreetName() == $StreetName
                    && $tblAddress->getCounty() == $County
//                    && $tblAddress->getNation() == $Nation
                    && $tblAddress->getTblCountry() == $tblCountry
                ) {
                    return $tblAddress;
                }
            }
        }

        $Entity = new TblAddress();
        $Entity->setStreetName($StreetName);
        $Entity->setStreetNumber($StreetNumber);
        $Entity->setPostOfficeBox($PostOfficeBox);
        $Entity->setRegion($Region);
        $Entity->setTblState($tblState);
        $Entity->setTblCity($tblCity);
        $Entity->setCounty($County);
        $Entity->setNation($Nation);
        $Entity->setAddressExtra($AddressExtra);
        $Entity->setTblCountry($tblCountry);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPerson  $tblPerson
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToPerson
     */
    public function addAddressToPerson(TblPerson $tblPerson, TblAddress $tblAddress, TblType $tblType, $Remark)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToPerson')
            ->findOneBy(array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_ADDRESS    => $tblAddress->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToPerson();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getAddressToPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getAddressToCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany', $Id);
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addAddressToCompany(TblCompany $tblCompany, TblAddress $tblAddress, TblType $tblType, $Remark)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToCompany')
            ->findOneBy(array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_ADDRESS     => $tblAddress->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                array(
                    TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
                ),
                // Hauptadressen zu erst
                array(TblToPerson::ATT_TBL_TYPE => self::ORDER_ASC)
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                array(
                    TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
                ),
                // Hauptadressen zu erst
                array(TblToPerson::ATT_TBL_TYPE => self::ORDER_ASC)
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType   $tblType
     *
     * @return bool|Entity\TblToPerson[]
     */
    public function getAddressAllByPersonAndType(TblPerson $tblPerson, TblType $tblType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $isForced
     *
     * @return bool|TblAddress
     */
    public function getAddressByPerson(TblPerson $tblPerson, $isForced = false)
    {

        $Type = $this->getTypeByName(TblType::META_MAIN_ADDRESS);
        $Parameter = array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATT_TBL_TYPE       => $Type->getId()
        );
        if($isForced) {
            /** @var TblToPerson $Entity */
            if(($Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                $Parameter))) {
                return $Entity->getTblAddress();
            } else {
                return false;
            }
        } else {
            /** @var TblToPerson $Entity */
            if(($Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson',
                $Parameter))) {
                return $Entity->getTblAddress();
            } else {
                return false;
            }
        }
    }

    /** get Deliver Address else Main Address
     *
     * @param TblPerson $tblPerson
     * @param bool      $isForced
     *
     * @return bool|TblAddress
     */
    public function getInvoiceAddressByPerson(TblPerson $tblPerson, $isForced = false)
    {

        $Type = $this->getTypeByName(TblType::META_INVOICE_ADDRESS);
        $Parameter = array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATT_TBL_TYPE       => $Type->getId()
        );
        if($isForced) {
            /** @var TblToPerson $Entity */
            if(($Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                $Parameter))) {
                return $Entity->getTblAddress();
            } else {
                return $this->getAddressByPerson($tblPerson);
            }
        } else {
            /** @var TblToPerson $Entity */
            if(($Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson',
                $Parameter))) {
                return $Entity->getTblAddress();
            } else {
                return $this->getAddressByPerson($tblPerson);
            }
        }
    }

    /** get Main Address (Type ID 1)
     *
     * @param TblPerson $tblPerson
     *
     * @return false|TblToPerson
     */
    public function getAddressToPersonByPerson(TblPerson $tblPerson)
    {

        // TODO: Persistent Types
        $Type = $this->getTypeById(1);

        /** @var TblToPerson $Entity */
        return $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_TYPE       => $Type->getId()
            ));
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType',
            array(
                TblType::ATTR_NAME => $Name
            ));
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblAddress
     */
    public function getAddressByCompany(TblCompany $tblCompany)
    {

        // TODO: Persistent Types
        $Type = $this->getTypeById(1);
        /** @var TblToPerson $Entity */
        if (( $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_TYPE        => $Type->getId()
            ))
        )
        ) {
            return $Entity->getTblAddress();
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ),
            // Hauptadressen zu erst
            array(TblToCompany::ATT_TBL_TYPE => self::ORDER_ASC)
        );
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return bool|Entity\TblToCompany[]
     */
    public function getAddressAllByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId()
            ));
    }

    /**
     * @param array  $ProcessList
     * @param string $CityName
     *
     * @return bool
     */
    public function updateCityAnonymousBulk($ProcessList, $CityName = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $Address){
                /** @var TblCity $tblCity */
                $tblCity = $Address['tblCity'];
                if($CityName){
                    $City = $CityName;
                } else {
                    $City = $Address['City'];
                }
                /** @var TblCity $Entity */
                $Entity = $Manager->getEntityById('TblCity', $tblCity->getId());
//                $Protocol = clone $Entity;
                if (null !== $Entity) {
                    $Entity->setName($City);
                    $Entity->setCode(str_pad(rand(00000, 99999), 5, '0', STR_PAD_LEFT));
                    $Entity->setDistrict('');
                    $Manager->bulkSaveEntity($Entity);
                    // no Protocol necessary
//                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
//                    $Protocol,
//                    $Entity);
                }
            }
            $Manager->flushCache();
            return true;
        }
        return false;
    }

    /**
     * @param array   $ProcessList
     *
     * @return bool
     */
    public function updateAddressAnonymousBulk($ProcessList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $Address){
                /** @var TblAddress $tblAddress */
                $tblAddress = $Address['tblAddress'];
                /** @var TblAddress $Entity */
                $Entity = $Manager->getEntityById('TblAddress', $tblAddress->getId());
//                $Protocol = clone $Entity;
                if (null !== $Entity) {
                    $Entity->setCounty('');
                    $Entity->setNation('');
                    $Entity->setPostOfficeBox('');
                    $Entity->setRegion('');
                    $Entity->setStreetNumber(rand(1,99));
                    $Entity->setTblState(null);
                    $Manager->bulkSaveEntity($Entity);
                    // no Protocol necessary
//                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
//                    $Protocol,
//                    $Entity);
                }
            }
            $Manager->flushCache();
            return true;
        }
        return false;
    }

    /**
     * @param TblAddress $tblAddress
     * @param ?TblCountry $tblCountry
     *
     * @return bool
     */
    public function updateAddressCountry(TblAddress $tblAddress,TblCountry $tblCountry = null): bool
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblAddress', $tblAddress->getId());
//        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblCountry($tblCountry);
            $Manager->saveEntity($Entity);
            // no Protocol necessary
//          Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
//              $Protocol,
//              $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param             $tblAddress
     * @param             $tblType
     * @param             $Remark
     *
     * @return bool
     */
    public function updateAddressToPerson(
        TblToPerson $tblToPerson,
        TblAddress $tblAddress,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool        $IsSoftRemove
     *
     * @return bool
     */
    public function removeAddressToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param             $tblAddress
     * @param             $tblType
     * @param             $Remark
     *
     * @return bool
     */
    public function updateAddressToCompany(
        TblToCompany $tblToCompany,
        TblAddress $tblAddress,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeAddressToCompany(TblToCompany $tblToCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAddress[] $tblAddressList
     * @return void
     */
    public function destroyAddressBulk(array $tblAddressList): void
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAddress $tblAddress */
        foreach($tblAddressList as $tblAddress){
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblAddress, true);
            $Manager->bulkKillEntity($tblAddress);
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array of TblAddress->Id
     */
    public function fetchIdAddressAllByPerson(TblPerson $tblPerson)
    {

        $Cache = $this->getCache(new RedisHandler());
        if (null === ( $IdList = $Cache->getValue($tblPerson->getId(), __METHOD__) )) {
            $Manager = $this->getConnection()->getEntityManager();

            $Builder = $Manager->getQueryBuilder();
            $Query = $Builder->select('L.tblAddress')
                ->from(__NAMESPACE__.'\Entity\TblToPerson', 'L')
                ->where($Builder->expr()->eq('L.serviceTblPerson', '?1'))
                ->setParameter(1, $tblPerson->getId())
                ->getQuery();

            $IdList = $Query->useQueryCache(true)->getResult(ColumnHydrator::HYDRATION_MODE);

            $Cache->setValue($tblPerson->getId(), $IdList, 0, __METHOD__);
        }

        return $IdList;
    }

    /**
     * @param array $IdArray of TblAddress->Id
     *
     * @return TblAddress[]
     */
    public function fetchAddressAllByIdList($IdArray)
    {

        $Key = md5(json_encode($IdArray));
        $Cache = $this->getCache(new RedisHandler());
        if (null === ( $tblAddressAll = $Cache->getValue($Key, __METHOD__) )) {

            $Manager = $this->getConnection()->getEntityManager();

            $Builder = $Manager->getQueryBuilder();
            $Query = $Builder->select('A')
                ->from(__NAMESPACE__.'\Entity\TblAddress', 'A')
                ->where($Builder->expr()->in('A.Id', '?1'))
                ->setParameter(1, $IdArray)
                ->getQuery();
            $tblAddressAll = $Query->useQueryCache(true)->getResult(IdHydrator::HYDRATION_MODE);

            $Cache->setValue($Key, $tblAddressAll, 0, __METHOD__);
        }

        return $tblAddressAll;
    }

    /**
     * @param array $personIdList of TblAddress->Id
     *
     * @return array
     */
    public function fetchAddressAllByPersonIdList($personIdList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $tblAddress = new TblAddress();
        $tblCity = new TblCity();
        $tblToPerson = new TblToPerson();
        $tblType = new TblType();

        $Builder = $Manager->getQueryBuilder();
        $Query = $Builder->select('tTP.serviceTblPerson as tblPersonId, tA.Id as tblAddressId, tA.StreetName, tA.StreetNumber, tA.StreetNumber, tC.Id as tblCityId, tC.Code, tC.Name, tC.District') //
            ->from($tblToPerson->getEntityFullName(), 'tTP')
            ->leftJoin($tblAddress->getEntityFullName(), 'tA', 'WITH', 'tA.Id = tTP.tblAddress')
            ->leftJoin($tblCity->getEntityFullName(), 'tC', 'WITH', 'tC.Id = tA.tblCity')
            ->leftJoin($tblType->getEntityFullName(), 'tT', 'WITH', 'tT.Id = tTP.tblType')
            ->where($Builder->expr()->in('tTP.serviceTblPerson', '?1'))
            ->andWhere($Builder->expr()->eq('tT.Name', '?2'))
            ->andWhere($Builder->expr()->isNull('tTP.EntityRemove'))
            ->setParameter(1, $personIdList)
            ->setParameter(2, 'Hauptadresse')
            ->getQuery();
        $result = $Query->getResult();
        $IdCorrectedResult = array();
        if(!empty($result)){
            foreach($result as $row){
                $IdCorrectedResult[$row['tblPersonId']] = $row;
            }
        }
        return $IdCorrectedResult;
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAddress $tblAddress
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonAllByAddress(TblAddress $tblAddress)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(TblToPerson::ATT_TBL_ADDRESS => $tblAddress->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblAddress $tblAddress
     *
     * @return false|TblToPerson
     */
    public function getAddressToPersonByPersonAndAddress(TblPerson $tblPerson, TblAddress $tblAddress)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATT_TBL_ADDRESS => $tblAddress->getId()
        ));
    }
}
