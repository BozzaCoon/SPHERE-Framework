<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\RadarChart;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\RadarChart as RadarChartGraphic;

/**
 * Class RadarChart
 *
 * Test-Dokument: bettet ein Netzdiagramm (SVG) in eine PDF ein und nutzt dabei
 * denselben DomPdf-Weg wie die Zeugnisse (AbstractDocument + Creator).
 *
 * Beschriftung, Werte und Maximalwert kommen über $Data herein und sind später
 * je Schüler dynamisch; hier sind Demo-Daten hinterlegt.
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\RadarChart
 */
class RadarChart extends AbstractDocument
{

    /** @var string[] $LabelList */
    private $LabelList;
    /** @var float[] $ValueList */
    private $ValueList;
    /** @var float $Max */
    private $Max;
    /** @var bool $IsInverted */
    private $IsInverted;

    /**
     * @param array $Data optional: 'Labels' (array), 'Values' (array), 'Max' (float), 'IsInverted' (bool)
     */
    public function __construct($Data = array())
    {

    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Netzdiagramm Testseite';
    }

    /**
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $part = '0'): Frame
    {

        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Page
     */
    private function buildPage()
    {
        // 1
        $RadarChartLabelList = array(
            "Netzdiagramm mit unterschiedlichen Paramtern",
            "Lern- und Einsatzbereitschaft",
            "Selbstorganisation",
            "Problemlösefähigkeit",
            "Zuverlässigkeit und\nVerantwortungsübernahme",
            "Kooperations- und\nTeamfähigkeit",
            "Kommunikations- und\nKonfliktfähigkeit",
        );
        $RadarChartValueList = array(3.5, 2, 3, 2, 2.5, 3, 2);
        // 2
        $RadarChartLabelList2 = array("Mathematik", "0 Test", "Biologie");
        $RadarChartValueList2 = array(15, 0, 4);
        // 3
        $RadarChartLabelList3 = array(
            "Mitarbeit",
            "Fleiß",
            "Ordnung",
            "Pünktlichkeit\nTest Umbruch\nRadius erweitert mehr Zeilen möglich",
            "Sauberkeit\n1\n2\n3\ngeht mit Platz",
            "Freundlichkeit\nund so"
        );
        $RadarChartValueList3 = array(2, 1, 6, 2, 3, 4);

        $Image = (new RadarChartGraphic($RadarChartLabelList, $RadarChartValueList, 3.5, 420, 280))->setRingCount(7)
            ->getImage();
        $Image2 = (new RadarChartGraphic($RadarChartLabelList2, $RadarChartValueList2, 15, 320, 220))->setColor('#DD6688')
            ->getImage();
        $Image3 = (new RadarChartGraphic($RadarChartLabelList3, $RadarChartValueList3, 6, 320, 220))->setInverted()->setFontSize(20)->setcolor('#33BBBB')
            ->getImage();
        $Image4 = (new RadarChartGraphic($RadarChartLabelList, $RadarChartValueList, 3.5, 420, 200))->setRingCount(7)
            ->getImage();
        $Image5 = (new RadarChartGraphic($RadarChartLabelList2, $RadarChartValueList2, 15, 320, 200))->setColor('#DD6688')
            ->getImage();
        $Image6 = (new RadarChartGraphic($RadarChartLabelList3, $RadarChartValueList3, 6, 320, 220))->setInverted()->setFontSize(19)->setcolor('#33BBBB')->setRadiusSpace(50)
            ->getImage();

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($this->getName())
                    ->styleTextSize('14pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->stylePaddingBottom('10px')
                )
                /** ToDO Schrift im Radarchart übernimmt den style der vorgehenden Schrift
                 (Überschrift -> bold) mit einer Zeile ohne höhe überschreibt man das wieder. */
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('0')
                )
                ->addElement((new Element())
                    ->setContent($Image)
                    ->styleAlignCenter()
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($Image2)
                        ->styleAlignCenter()
                    , '50%')
                    ->addElementColumn((new Element())
                        ->setContent($Image3)
                        ->styleAlignCenter()
                    , '50%')
                )
                ->addElement((new Element())
                    ->setContent('Rand Test')
                )
                ->addElement((new Element())
                    ->setContent($Image4)
                    ->styleAlignCenter()
                    ->styleBorderAll()
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($Image5)
                        ->styleAlignCenter()
                        ->styleBorderAll()
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent($Image6)
                        ->styleAlignCenter()
                        ->styleBorderAll()
                        , '50%')
                )
                ->addElement((new Element())
                    ->setContent('Schrift kann in der Breite und überschreiten des RadiusSpace des Bildes aus den Dimensionen ausbrechen<br/>
                                  Radiuspace lässt sich korrigieren, Breite wird im PDF gerendert obwohl das Bild nicht so breit eingestellt ist.')
                )
            );
    }
}
