<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class FosAbg extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $textSize = self::TEXT_SIZE_LARGE;
        $textSize2 = '17px';
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school[] = 'Berufliches Schulzentrum';
        $school[] = 'der HOGA Schloss Albrechtsberg g SchulgmbH';
        $school[] = 'Staatlich anerkannte Schulen in freier Trägerschaft';

        return (new Page())
            ->addSlice($this->getHeader($school))
            ->addSlice($this->getCustomFosTitle($personId, 'Abgangszeugnis', '5px', false))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            '{{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                            {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }}'
                            , '22px'
                        )
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('10px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('geboren am', $textSize), '20%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ), '20%')
                    ->addElementColumn($this->getElement('&nbsp;'))
                    ->addElementColumn($this->getElement(
                        'in &nbsp;&nbsp;&nbsp;
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ), '35%')
                )
            )
            ->addSlice((new Slice())
                ->addElement($this->getElement('hat vom', $textSize)->styleAlignCenter())
                ->addElement($this->getElement('den zweijährigen Bildungsgang der', $textSize)->styleAlignCenter())
                ->addElement($this->getElement('Fachoberschule', $textSize2)->styleAlignCenter()->styleTextBold())
                ->addElement($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                        Fachrichtung {{ Content.P' . $personId . '.Input.SubjectArea }}
                    {% else %}
                        Fachrichtung &ndash;
                    {% endif %}'
                    , $textSize2)->styleAlignCenter()->styleTextBold()->styleMarginTop('-5px'))
                ->addElement($this->getElement('besucht und folgende Leistungen erreicht', $textSize)->styleAlignCenter())
            )
            ->addSlice($this->getCustomFosSubjectLanes($personId, '5px', true, false)->styleHeight('260px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Bemerkungen:', $textSize)
                        ->styleTextUnderline()
                        , '20%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                            {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ))
                )
            )
            ->addSlice($this->getCustomFosSkilledWork($personId))
            ->addSlice((new Slice())
                ->addElement($this->getElement(
                    'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.', $textSize
                ))
            )
            ->addSlice($this->getCustomFosSignPart($personId))
            ->addSlice($this->getCustomFosInfo());
    }
}