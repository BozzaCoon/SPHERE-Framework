<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:19
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E04_1
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E04.1 Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Anzahl der Fremsprachen')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '50%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                            , '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px')
                            ->styleBorderRight()
                            , '25%'
                        )
                    ), '40%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('Vorb.kl. u.<br/>-gr. f.<br/>Migranten')
//                    ->styleBorderRight(), '10%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleTextBold()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '10%'
                )
            );

        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0: $text = 'keine'; break;
                case 1: $text = 'eine'; break;
                case 2: $text = 'zwei'; break;
                case 3: $text = 'drei'; break;
                case 4: $text = 'vier und mehr'; break;
                default: $text = '&nbsp;';
            }

            $section = new Section();

            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '50%'
                );

            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E04_1.F' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E04_1.F' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '10%'
                    );
            }

            $section
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E04_1.F' . $i . '.Migration is not empty) %}
//                            {{ Content.E04_1.F' . $i . '.Migration }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleBackgroundColor('lightgrey')
//                    ->styleBorderRight(), '10%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04_1.F' . $i . '.TotalCount is not empty) %}
                                {{ Content.E04_1.F' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey'), '10%'
                );

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        /**
         * Total
         */
        $section = new Section();

        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '50%'
            );

        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04_1.TotalCount.L' . $level . ' is not empty) %}
                                {{ Content.E04_1.TotalCount.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '10%'
                );
        }

        $section
//            ->addElementColumn((new Element())
//                ->setContent('
//                    {% if (Content.E04_1.TotalCount.Migration is not empty) %}
//                        {{ Content.E04_1.TotalCount.Migration }}
//                    {% else %}
//                        &nbsp;
//                    {% endif %}
//                ')
//                ->styleBackgroundColor('lightgrey')
//                ->styleBorderRight(), '10%'
//            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor('lightgrey'), '10%'
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        return $sliceList;
    }
}