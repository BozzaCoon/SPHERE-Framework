<?php
namespace SPHERE\Application\Transfer;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Export\Export;
use SPHERE\Application\Transfer\Import\Import;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Application\Transfer\Indiware\Indiware;
use SPHERE\Application\Transfer\Untis\Untis;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\Transfer
 */
class Transfer implements IClusterInterface
{

    public static function registerCluster()
    {

        Import::registerApplication();
        Export::registerApplication();
        Untis::registerApplication();
        Indiware::registerApplication();
        Education::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datentransfer'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Datentransfer');

        $Json = '{
  "Gesamtexport": {
    "Informationen": {
      "Version": "1.1"
    },
    "Vertretungsplan": {
      "Vertretungsplan": [
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2025-01-13.json",
            "Titel": "Montag, 13. Januar 2025 (B-Woche) ",
            "Schulname": "Evangelische Oberschule Schneeberg",
            "Datum": "13.01.2025",
            "Erstellt": "13.01.2025, 12:31",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "AngSt",
                  "Grund": "So"
                },
                {
                  "Kurz": "Landg",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "Laube",
                  "Grund": "Proje"
                }
              ],
              "LehrerMitAenderung": [
                {
                  "Kurz": "Eiser"
                },
                {
                  "Kurz": "Heim"
                },
                {
                  "Kurz": "Höfer"
                },
                {
                  "Kurz": "Kröb"
                },
                {
                  "Kurz": "Ried"
                },
                {
                  "Kurz": "Schäd"
                },
                {
                  "Kurz": "Uhlig"
                }
              ],
              "KlassenMitAenderung": [
                {
                  "Kurz": "5b"
                },
                {
                  "Kurz": "6a"
                },
                {
                  "Kurz": "6b"
                },
                {
                  "Kurz": "7a"
                },
                {
                  "Kurz": "7b"
                },
                {
                  "Kurz": "9a"
                },
                {
                  "Kurz": "10a"
                }
              ]
            }
          },
          "Aktionen": [
            {
              "Ak_Id": 3304,
              "Ak_UntNr": 46,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 2,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "SPO",
              "Ak_VFach": "SPO",
              "Klassen": [
                "5b"
              ],
              "VKlassen": [
                "5b"
              ],
              "Lehrer": [
                "Landg"
              ],
              "VLehrer": [
                "Ried"
              ],
              "Raeume": [
                "TH 1"
              ],
              "VRaeume": [
                "TH 1"
              ]
            },
            {
              "Ak_Id": 2542,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "",
              "Ak_VFach": "REe",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "Heim"
              ],
              "VLehrer": [
                "Heim"
              ],
              "Raeume": [],
              "VRaeume": [
                "312"
              ]
            },
            {
              "Ak_Id": 2541,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 7,
              "Ak_Fach": "",
              "Ak_VFach": "GEO",
              "Klassen": [
                "6b"
              ],
              "VKlassen": [
                "6b"
              ],
              "Lehrer": [
                "Eiser"
              ],
              "VLehrer": [
                "Eiser"
              ],
              "Raeume": [],
              "VRaeume": [
                "313"
              ]
            },
            {
              "Ak_Id": 2538,
              "Ak_UntNr": 81,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_Fach": "DE",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7a"
              ],
              "VKlassen": [
                "7a"
              ],
              "Lehrer": [
                "Uhlig"
              ],
              "VLehrer": [
                "Uhlig"
              ],
              "Raeume": [
                "210"
              ]
            },
            {
              "Ak_Id": 2540,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7a"
              ],
              "VKlassen": [
                "7a"
              ],
              "Lehrer": [
                "Uhlig"
              ],
              "VLehrer": [
                "Uhlig"
              ],
              "Raeume": []
            },
            {
              "Ak_Id": 2539,
              "Ak_UntNr": 85,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_Fach": "GEO",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7a"
              ],
              "VKlassen": [
                "7a"
              ],
              "Lehrer": [
                "Uhlig"
              ],
              "VLehrer": [
                "Uhlig"
              ],
              "Raeume": [
                "210"
              ]
            },
            {
              "Ak_Id": 2535,
              "Ak_UntNr": 100,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_Fach": "REe",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7b"
              ],
              "VKlassen": [
                "7b"
              ],
              "Lehrer": [
                "Höfer"
              ],
              "VLehrer": [
                "Höfer"
              ],
              "Raeume": [
                "213"
              ]
            },
            {
              "Ak_Id": 2536,
              "Ak_UntNr": 98,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_Fach": "DE",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7b"
              ],
              "VKlassen": [
                "7b"
              ],
              "Lehrer": [
                "Kröb"
              ],
              "VLehrer": [
                "Kröb"
              ],
              "Raeume": [
                "213"
              ]
            },
            {
              "Ak_Id": 2537,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 7,
              "Ak_Fach": "",
              "Ak_VFach": "ZW",
              "Klassen": [
                "7b"
              ],
              "VKlassen": [
                "7b"
              ],
              "Lehrer": [
                "Heim"
              ],
              "VLehrer": [
                "Heim"
              ],
              "Raeume": []
            },
            {
              "Ak_Id": 3308,
              "Ak_UntNr": 10,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_Fach": "BIO",
              "Ak_VFach": "BIO",
              "Klassen": [
                "9a"
              ],
              "VKlassen": [
                "9a"
              ],
              "Lehrer": [
                "Landg"
              ],
              "VLehrer": [
                "Schäd"
              ],
              "Raeume": [
                "Bio"
              ],
              "VRaeume": [
                "Bio"
              ]
            },
            {
              "Ak_Id": 3306,
              "Ak_UntNr": 182,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Ak_VFach": "BIO",
              "Klassen": [
                "10a"
              ],
              "VKlassen": [
                "10a"
              ],
              "Lehrer": [
                "Landg"
              ],
              "VLehrer": [
                "Schäd"
              ],
              "Raeume": [
                "Bio"
              ],
              "VRaeume": [
                "Bio"
              ]
            }
          ]
        }
      ]
    }
  }
}';

        Replacement::useService()->importJsonReplacement($Json);

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Transfer'));

        return $Stage;
    }
}
