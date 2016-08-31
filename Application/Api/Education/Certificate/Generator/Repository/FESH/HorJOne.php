<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FESH;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class HorJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class HorJOne extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Hormersdorf Jahreszeugnis Klasse 1.pdf')
                    ->styleTextSize('12px')
                    ->styleTextColor('#CCC')
                    ->styleAlignCenter()
                    , '25%')
                ->addElementColumn((new Element\Sample())
                    ->styleTextSize('30px')
                )
                ->addElementColumn((new Element())
                    , '25%')
            );

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $IsSample ? $Header : new Slice()
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/Hormersdorf_logo.jpg', '100px'))
                            ->styleAlignCenter()
                            , '25%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Name der Schule:')
                                    ->styleTextSize('11px')
                                    ->styleMarginTop('6px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('Freie Evangelische Grundschule Hormersdorf')
                                    ->styleTextSize('17px')
                                    ->styleTextBold()
                                    ->styleBorderBottom('1px', '#BBB')
                                    ->styleAlignCenter()
                                    , '80%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    , '27%')
                                ->addElementColumn((new Element())
                                    ->setContent('(Staatlich anerkannte Ersatzschule)')
                                    ->styleAlignCenter()
                                    , '73%')
                            )
                            ->styleMarginTop('30px')
                            , '75%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('JAHRESZEUGNIS')
                        ->styleTextSize('24px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('DER GRUNDSCHULE')
                        ->styleTextSize('24px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleBorderBottom('1px', '#BBB')
                            , '8%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '47%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '15%')
                    )->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '80%')
                    )->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('410px')
                        ->styleMarginTop('15px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '17%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '50%')
                    )->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            , '25%')
                        ->addElementColumn((new Element())
                            , '65%')
                    )->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienststempel der Schule')
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer/in')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            ->styleAlignCenter()
                            , '35%')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleBorderBottom('1px', '#BBB')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Personensorgeberechtigte/r')
                        ->styleAlignCenter()
                    )
                )
            )
        );
    }
}
