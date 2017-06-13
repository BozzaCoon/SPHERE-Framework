<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;

/**
 * Class MsAbsHs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsAbsHs extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        if (($tblConsumer = Consumer::useService()->getConsumerBySession())
            && $tblConsumer->getAcronym() == 'ESZC'
        ) {
            $isSchoolNameBold = true;
        } else {
            $isSchoolNameBold = false;
        }

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABSCHLUSSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('32%')
                    ->styleTextBold()
                )
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                                ')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('50px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                                ')
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                {{ Content.P' . $personId . '.Person.Address.City.Name }}
                            ')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                        ->styleTextBold($isSchoolNameBold ? 'bold' : 'normal')
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px')
                        ->setContent('&nbsp;')
                        , '5%')
                )
                ->styleMarginTop('35px')
            )
            ->addSlice(
                (new Slice())
                    ->addElement(
                        (new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Company.Address.Street.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px')
                            ->styleAlignCenter()
                    )
                    ->styleMarginTop('10px')
            )
            ->addSlice(
                (new Slice())
                    ->addSection(
                        (new Section())
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBorderBottom('1px')
                                , '10%')
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('{% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                            {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                    ->styleBorderBottom('1px')
                                    ->styleAlignCenter()
                            )
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('besucht')
                                    ->styleAlignRight()
                                , '10%')
                    )
                    ->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Name und Anschrift der Schule')
                    ->styleTextSize('9px')
                    ->styleTextColor('#999')
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                    ->styleMarginBottom('5px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und hat an der besonderen Leistungsfeststellung in der Klassenstufe 9 der 
                        Schulart Mittelschule teilgenommen und den')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                )
                ->addElement((new Element())
                    ->setContent('HAUPTSCHULABSCHLUSS')
                    ->styleMarginTop('18px')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent('erworben.')
                    ->styleMarginTop('20px')
                    ->styleAlignLeft()
                )
                ->styleAlignCenter()
                ->styleMarginTop('20%')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.Salutation }}
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                        ->styleBorderBottom()
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}
                            ')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                    )
                )->styleMarginTop('50px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '225px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Der Prüfungsausschuss')
                        ->styleAlignCenter()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleMarginTop('10px')
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleMarginTop('10px')
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorsitzende(r)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mitglied')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleMarginTop('15px')
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Mitglied')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
            )
            ->addSlice($this->getInfo('120px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend')
            );

        return $pageList;
    }
}
