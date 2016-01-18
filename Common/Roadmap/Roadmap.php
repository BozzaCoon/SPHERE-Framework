<?php
namespace SPHERE\Common\Roadmap;

use SPHERE\Common\Roadmap\Milestone\Major0Minor8;
use SPHERE\Common\Roadmap\Milestone\Major0Minor9;
use SPHERE\Common\Roadmap\Milestone\Major1Minor0;
use SPHERE\Common\Roadmap\Milestone\Major1Minor1;
use SPHERE\Common\Roadmap\Milestone\Major1Minor2;
use SPHERE\Common\Roadmap\Milestone\Major1Minor3;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Roadmap
 *
 * @package SPHERE\Application
 */
class Roadmap extends Extension
{

    /** @var null|RoadmapExtension $Roadmap */
    private $Roadmap = null;

    /**
     *
     */
    public function __construct()
    {

        $this->Roadmap = $this->getRoadmap();

        Major0Minor8::definePatch0($this->Roadmap);
        Major0Minor8::definePatch0Fix1($this->Roadmap);
        Major0Minor8::definePatch1($this->Roadmap);
        Major0Minor8::definePatch2($this->Roadmap);

        Major0Minor9::definePatch0($this->Roadmap);
        Major0Minor9::definePatch1($this->Roadmap);

        Major1Minor0::definePatch0($this->Roadmap);
        Major1Minor0::definePatch1($this->Roadmap);
        Major1Minor1::definePatch0($this->Roadmap);
        Major1Minor2::definePatch0($this->Roadmap);
        Major1Minor3::definePatch0($this->Roadmap);

        $this->poolMajor1MinorXPatchX();
    }

    /**
     * Version 1.x.x
     * To be released 2016
     */
    private function poolMajor1MinorXPatchX()
    {

        $Release = $this->Roadmap->createRelease('1.x.x', 'KREDA (Ziel 2016)');

        $Category = $Release->createCategory('Datentransfer', 'Austausch mit anderen Programmen');

        $Feature = $Category->createFeature('Indiware', 'Import von Daten');

        $Feature->createTask('Import von Lehraufträgen', 'Zusätzlich zur Vergabe in Kreda')
            ->createDuty('Analyse von Indiware')
            ->createDuty('Analyse von Exportfunktion')
            ->createDuty('Analyse der Daten');

        $Feature = $Category->createFeature('Fuxschool', 'Import von Daten');

        $Feature->createTask('Import von Personendaten', 'Zusätzlich zur Eingabe in Kreda')
            ->createDuty('Analyse von Fuxschool', true)
            ->createDuty('Analyse der Exportfunktion', true)
            ->createDuty('Analyse der Daten', false);

        $Category = $Release->createCategory('Auswertungen');
        $Feature = $Category->createFeature('Dynamische Auswertungen');
        $Feature->createTask('Report-Designer');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');
        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: People')
            ->createDuty('Klären welcher Inhalt enthalten sein soll', false);

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: Corporation')
            ->createDuty('Klären welcher Inhalt enthalten sein soll', false);

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Klassen')
            ->createDuty('Sitzplan');

        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: Education')
            ->createDuty('Klären welcher Inhalt enthalten sein soll', false);
        $Feature->createTask('Board: Lesson')
            ->createDuty('Klären welcher Inhalt enthalten sein soll', false);
        $Feature->createTask('Board: Subject')
            ->createDuty('Klären welcher Inhalt enthalten sein soll', false);

        // Diverses
        $Category = $Release->createCategory('Diverses');
        $Feature = $Category->createFeature('Letzte Änderung der Daten anzeigen');
        $Feature->createTask('Person')
            ->createDuty('in Metadaten')
            ->createDuty('Klären was / wo wirklich sinnvoll / machbar ist', false);

        // Plattform
        $Category = $Release->createCategory('Plattform');
        $Feature = $Category->createFeature('Archiv');
        $Feature->createTask('Allowed memory size')
            ->createDuty('Speicherverbrauch minimieren');
        $Feature = $Category->createFeature('Wiederherstellung');
        $Feature->createTask('Zurücksetzen')
            ->createDuty('Snapshot der Datenbanken des Mandanten erstellen')
            ->createDuty('Datenbanken des Mandanten auf früheren Snapshot zurücksetzen');
    }

    /**
     * @return Stage
     */
    public function frontendMap()
    {

        return $this->Roadmap->getStage();
    }

    /**
     * @return null|RoadmapExtension
     */
    public function getRoadmapObject()
    {

        return $this->Roadmap;
    }

    /**
     * @return string
     */
    public function getVersionNumber()
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Version = $Cache->getValue('Version', __METHOD__))) {
            $Version = $this->Roadmap->getVersionNumber();
            $Cache->setValue('Version', $Version, 0, __METHOD__);
        }
        return $Version;
    }
}
