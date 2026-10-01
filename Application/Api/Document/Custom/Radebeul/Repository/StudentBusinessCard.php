<?php
namespace SPHERE\Application\Api\Document\Custom\Radebeul\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Center;

/**
 * Class StudentBusinessCard
 *
 * @package SPHERE\Application\Api\Document\Custom\Radebeul\Repository
 */
class StudentBusinessCard extends AbstractDocument
{

    const TEXT_SIZE = '16px';

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schüler Ausweis';
    }

    /**
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
    {

        // Visitenkartenformat 85mm x 55mm (bei 96dpi 322px x 208px), überschreibt das A4 aus Creator::buildDummyFile
        // Page auf Kartenhöhe begrenzen, damit überstehender Inhalt abgeschnitten wird statt umzubrechen
        return (new Frame('@page { size: 322px 208px; margin: 0; } div.Page { height: 208px; overflow: hidden; }'))->addDocument((new Document())
            ->addPage($this->buildPageOne())
        );
    }

    public function buildPageOne()
    {

        $fontFamily = 'MetaPro';
        $textSize = '16px';
        $PaddingBottom = '10px';
        $PaddingBottomLarge = '46px';

        $ColorTop = '#15416a';
        $ColorBottom = '#cb214c';
        $ColorSpring = '#40ae49';
        $ColorSummer = '#fff101';
        $ColorAuthum = '#882f36';
        $ColorWinter = '#069edb';

        return (new Page())
        ->addSlice((new Slice())
            ->addElement((new Element())
                // Höhenbudget 208px: Kopf 40px (24px + 2x 8px Padding) + Section 151px = 191px
                ->setContent('<span style="letter-spacing: 9px;">SCHÜLERAUSWEIS</span>')
                ->styleHeight('24px')
                ->styleTextBold()
                ->styleTextSize('16px')
                ->styleTextColor('#FFFFFF')
                ->styleFontFamily($fontFamily)
                ->styleAlignCenter()
                ->stylePaddingTop('6px')
                ->stylePaddingBottom('2px')
                ->styleBackgroundColor($ColorTop)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('')
                    ->styleHeight('151px')
                    ->styleBackgroundColor($ColorSpring)
                    , '7%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                            ->setContent('Evangelisches Schulzentrum Radebeul')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
                    ->stylePaddingTop('57px')
                    ->styleAlignCenter()
                    ->styleTextSize('12px')
                    , '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                            ->setContent('Evangelisches Schulzentrum Radebeul')
//                    ->styleFontFamily($fontFamily)
//                    ->styleTextBold()
//                    ->stylePaddingTop('57px')
//                    ->styleAlignCenter()
//                    ->styleTextSize('12px')
                    , '63%'
                )
//                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSRDokument.jpg',
//                    '301px', '90px'))
//                    ->stylePaddingTop('10px')
//                    ->styleHeight('10px')
//                    , '20%'
//                )
            )
//            ->addElement((new Element())
//                ->setContent('Abholvollmacht')
//                ->styleFontFamily($fontFamily)
//                ->styleTextBold()
//                ->stylePaddingTop()
//                ->styleAlignCenter()
//                ->styleTextSize('23px')
//                ->styleHeight('100px')
//            )
        );
    }
}