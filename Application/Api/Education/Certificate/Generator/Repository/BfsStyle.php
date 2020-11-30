<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

/**
 * Class BfsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
abstract class BfsStyle extends Certificate
{

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    protected function getSchoolHead($personId, $CertificateName = 'Halbjahreszeugnis', $isChangeableCertificateName = false, $IsLogo = false)
    {

        $name = '';
        $secondLine = '';
        // get company name
        if(($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson,
                $this->getTblDivision() ? $this->getTblDivision() : null))
        ){
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        if($IsLogo){
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '61%')
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                    '214px', '66px'))
                    ->styleAlignRight()
                    ->stylePaddingTop('20px')
                    , '39%')
            );
            $Slice->addElement((new Element())
                ->setContent($name ? $name : '&nbsp;')
                ->styleAlignRight()
                ->styleTextSize('22px')
                ->styleHeight('28px')
                ->stylePaddingTop('40px')
            );
            $Slice->addElement((new Element())
                ->setContent($secondLine ? $secondLine : '&nbsp;')
                ->styleAlignRight()
                ->styleTextSize('18px')
                ->styleHeight('42px')
//            ->stylePaddingTop('20px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($name ? $name : '&nbsp;')
                ->styleAlignCenter()
                ->styleTextSize('22px')
                ->styleHeight('28px')
                ->stylePaddingTop('25px')
            );
            $Slice->addElement((new Element())
                ->setContent($secondLine ? $secondLine : '&nbsp;')
                ->styleAlignCenter()
                ->styleTextSize('18px')
                ->styleHeight('42px')
//            ->stylePaddingTop('20px')
            );
        }

        $Slice->addSection($this->getIndividuallyLogo($this->isSample()));
        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName)
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        }
        $PreString = 'der Berufsfachschule für';
        if($CertificateName === 'Abschlusszeugnis'){
            $PreString = 'der Berufsfachschule';
        }
        $Slice->addElement((new Element())
            ->setContent($PreString.' {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% endif %}')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );



        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    protected function getSchoolHeadAbg($personId, $CertificateName = 'Abgangszeugnis', $isChangeableCertificateName = false)
    {

        $name = '';
        $secondLine = '';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $this->getTblDivision() ? $this->getTblDivision() : null))
        ) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($name ? $name : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('22px')
            ->styleHeight('28px')
            ->stylePaddingTop('25px')
        );
        $Slice->addElement((new Element())
            ->setContent($secondLine ? $secondLine : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleHeight('42px')
//            ->stylePaddingTop('20px')
        );
        $Slice->addSection($this->getIndividuallyLogo($this->isSample()));
        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName)
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        }
        $Slice->addElement((new Element())
            ->setContent('der Berufsfachschule ')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param string $period
     *
     * @return Slice
     */
    protected function getStudentHead($personId, $period = 'Schulhalbjahr', $LastLineText = '', $isPreText = false)
    {

        $Slice = new Slice();

        if($isPreText){
            $Text = 'hat im zurückliegenden '.$period.' '.$LastLineText;
        } else {
            $Text = $LastLineText;
        }

        $Slice->stylePaddingTop('20px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('0.5px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->stylePaddingTop('20px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );
        $Slice->addElement((new Element())
            ->setContent($Text)
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
            ->styleBorderBottom('0.5px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getStudentHeadAbs($personId)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('20px');

        $Slice->addElement((new Element())
            ->setContent('
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('0.5px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );
        $Slice->addElement((new Element())
            ->setContent('hat vom 
                {% if(Content.P' . $personId . '.Input.DateFrom is not empty) %}
                    {{ Content.P' . $personId . '.Input.DateFrom }}
                {% else %}
                    ---
                {% endif %}
                bis 
                {% if(Content.P' . $personId . '.Input.DateTo is not empty) %}
                    {{ Content.P' . $personId . '.Input.DateTo }}
                {% else %}
                    ---
                {% endif %}
                 die')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('Berufsfachschule für {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% else %}
                    ---
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
        );
        $GenderString = 'Er/Sie';
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblGender = $tblPerson->getGender())){
                if($tblGender->getName() == 'Männlich'){
                    $GenderString = 'Er';
                } elseif($tblGender->getName() == 'Weiblich') {
                    $GenderString = 'Sie';
                }
            }
        }

        $Slice->addElement((new Element())
            ->setContent('besucht und im Schuljahr
                {% if(Content.P' . $personId . '.Input.AbsYear is not empty) %}
                    {{ Content.P' . $personId . '.Input.AbsYear }}
                {% else %}
                    ---
                {% endif %}
                die Abschlussprüfung bestanden. <br/>'.$GenderString.'
                ist berechtigt, die Berufsbezeichnung')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );

        $Slice->addElement((new Element())
            ->setContent('
                {% if(Content.P' . $personId . '.Input.ProfessionalTitle is not empty) %}
                    {{ Content.P' . $personId . '.Input.ProfessionalTitle }}
                {% else %}
                    ---
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
        );

        $Slice->addElement((new Element())
            ->setContent('zu führen.')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );
         

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getStudentHeadAbg($personId)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('20px');

        $Slice->addElement((new Element())
            ->setContent('
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('0.5px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );
        //toDO Zeitraum eintragen & erhalten
        $Slice->addElement((new Element())
            ->setContent('hat von "Datum1" bis "Datum2" die')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );
        //toDO BfsDestination erhalten
        $Slice->addElement((new Element())
            ->setContent('Berufsfachschule für "Platzhalter"{% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('besucht und folgende Leistungen erreicht:')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
            ->styleBorderBottom('0.5px')
        );

        return $Slice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Title
     * @param int            $StartSubject
     * @param int            $DisplaySubjectAmount
     * @param int            $SubjectRankingFrom
     * @param int            $SubjectRankingTill
     * @param string         $Height
     *
     * @return Slice
     */
    protected function getSubjectLineAcross($personId, TblCertificate $tblCertificate, $Title = 'Berufsübergreifender Bereich', $StartSubject = 1,
        $DisplaySubjectAmount = 6, $SubjectRankingFrom = 1, $SubjectRankingTill = 4, $Height = '160px')
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        // Grade Exists? => Add Subject to Certificate
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            $CountSubjectMissing = $DisplaySubjectAmount;

            // Berufsübergreifender Bereich
            $SubjectList = array();
            ksort($SubjectStructure);
            $SubjectCount = 1;
            foreach ($SubjectStructure as $Ranking => $SubjectListTemp) {
                foreach ($SubjectListTemp as $Lane => $Subject) {
                    if($SubjectCount >= $StartSubject
                        && $CountSubjectMissing != 0){
                        $SubjectList[$Ranking][$Lane] = $Subject;
                        $CountSubjectMissing--;
                    }
                    $SubjectCount++;
                }
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $SubjectListAlign) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectListAlign);
                $SubjectSection = (new Section());
                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["02"])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectListAlign as $Lane => $Subject) {
                    if ($Lane > 1){
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('15px')
                        ->stylePaddingBottom('1px')
                        ->styleTextSize($TextSize)
                        ->styleBorderBottom('0.5px')
                        , '39%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleMarginTop('15px')
                        ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                        ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');
                }
                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["01"])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $Slice->addSection($SubjectSection);
            }
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @return Slice
     */
    protected function getSubjectLinePerformance()
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Leistungen')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('20px')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    protected function getSubjectLineDuty()
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Pflichtbereich')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleTextBold()
            ->stylePaddingTop('30px')
        );

        return $Slice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Title
     * @param int            $StartSubject
     * @param int            $DisplaySubjectAmount
     * @param string         $Height
     * @param int            $SubjectRankingFrom
     * @param int            $SubjectRankingTill
     *
     * @return Slice
     */
    protected function getSubjectLineBase($personId, TblCertificate $tblCertificate, $Title = '&nbsp;', $StartSubject = 1,
        $DisplaySubjectAmount = 10, $Height = 'auto', $SubjectRankingFrom = 5, $SubjectRankingTill = 12)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = $DisplaySubjectAmount;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();
            ksort($SubjectStructure);

            $SubjectCount = 1;
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                if($SubjectCount >= $StartSubject
                    && $CountSubjectMissing != 0){
                    $SubjectList[$RankingLane] = $Subject;
                    $CountSubjectMissing--;
                }
                $SubjectCount++;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';

            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent($Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }
        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     * @param bool   $isChangeableCertificateName
     *
     * @return Slice
     */
    protected function getSecondPageHead($personId, $CertificateName = 'Halbjahresinformation', $isChangeableCertificateName = false)
    {

        $Slice = new Slice();

        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}' . ' für ' .
//            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
//                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
//            {% else %}
//                Frau/Herr
//            {% endif %}
                    '{{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }},
            geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName.
                ' für {{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }},
                geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        }



        return $Slice;
    }

    /**
     * @param        $personId
     * @param TblCertificate $tblCertificate
     * @param string $Height
     *
     * @return Slice
     */
    protected function getSubjectLineChosen($personId, TblCertificate $tblCertificate, $Height = '130px')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Wahlpflichtbereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();
        $CountSubjectMissing = 0;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 13
                        && $tblCertificateSubject->getRanking() < 15) {

                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            $CountSubjectMissing = 2;
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                $SubjectList[] = $Subject;
                $CountSubjectMissing--;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent($Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    private function getEmptySubjectField(Slice $Slice, $count = 0)
    {

        $TextSize = '14px';
        for($i = 0; $i < $count; $i++){
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop()
                ->styleMarginTop('10px')
                ->stylePaddingBottom('1px')
                ->styleTextSize($TextSize)
                ->styleBorderBottom('0.5px')
                , '91%');


            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor('#BBB')
                ->styleMarginTop('10px')
                ->stylePaddingTop('2px')
                ->stylePaddingBottom('1.5px')
                ->styleTextSize($TextSize)
                , '9%');
            $Slice->addSection($Section);
        }
        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     *
     * @return Slice
     */
    protected function getPraktika($personId, TblCertificate $tblCertificate)
    {

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);

        $Subject = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 15) {

                        // Wird immer ausgewiesen (Fach wird nicht abgebildet)
                        $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }
            $Subject = current($SubjectStructure);
        }

        $TextSize = '14px';
        $TextSizeSmall = '8px';

        $Slice = new Slice();
        $Slice->styleBorderAll('0.5px');
        $Slice->styleMarginTop('30px');
        $Slice->stylePaddingTop('10px');
        $Slice->stylePaddingBottom('10px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>Berufspraktische Ausbildung</b> (Dauer: 
                    {{ Content.P' . $personId . '.Input.OperationTime1 + Content.P' . $personId . '.Input.OperationTime2 + Content.P' . $personId . '.Input.OperationTime3 }}
                    Wochen)')
                ->stylePaddingLeft('5px')
                , '91%'
            )
            ->addElementColumn((new Element())      //ToDO richtiges Acronym auswählen
                ->setContent(empty($Subject) ? '&ndash;'
                         :'{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->stylePaddingTop(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                ->stylePaddingBottom(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 6px
                             {% else %}
                                 2px
                             {% endif %}')
                ->styleTextSize(empty($Subject) ? $TextSize
                            :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                )
                , '9%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation1 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime1 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation2 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime2 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('Dauer gesamt: {{ Content.P' . $personId . '.Input.OperationTime1 + Content.P' . $personId . '.Input.OperationTime2 + Content.P' . $personId . '.Input.OperationTime3 }} Wochen')
                ->stylePaddingTop('10px')
                ->styleAlignRight()
                ->stylePaddingRight('15px')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation3 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation3 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime3 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime3 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $Height
     *
     * @return Slice
     */
    protected function getDescriptionBsContent($personId, $Height = '195px')
    {

        $Slice = new Slice();

        $Slice->styleMarginTop('20px');
        $Slice->stylePaddingTop('5px');
        $Slice->styleHeight($Height);
        $Slice->styleBorderAll('0.5px');

        $Slice->addElement((new Element())
            ->setContent('Bemerkungen:')
            ->styleTextUnderline()
            ->stylePaddingLeft('5px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                        {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleAlignJustify()
            ->stylePaddingLeft('5px')
            ->stylePaddingRight('5px')
        );

        return $Slice;
    }

    /**
     * @param      $personId
     *
     * @param bool $isChairPerson Abgangszeugnis
     *
     * @return Slice
     */
    protected function getIndividuallySignPart($personId, $isChairPerson = false)
    {
        $Slice = (new Slice());

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Company.Address.City.Name }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Input.Date }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%')
        )
            ->styleMarginTop('25px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ort')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Siegel')
                    ->styleTextColor('gray')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '20%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Datum')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );

        $marginTop = '40px';
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px')
                , '35%')
        );
        if($isChairPerson){
            // Abgangszeugnis
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorsitzende/r des Prüfungsausschusses'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter(in)
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '&nbsp;'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
            );
        } else {
            // Standard Zeugnis
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter(in)
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                        {% else %}
                            Klassenlehrer(in)
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
            );
            $Slice->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('30px')
            );

            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    , '27%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('0.5px')
                    , '73%'
                )
            );

            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '27%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Eltern')
                    ->styleTextSize('10px')
                    ->styleAlignCenter()
                    , '73%'
                )
            );
        }

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getBottomInformation($personId)
    {

        $Slice = new Slice();

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}'
                )
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Ort, Datum')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                        {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                    {% else %}
                        Klassenlehrer/in
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '40%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleHeight('30px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px')
                , '73%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('Eltern')
                ->styleTextSize('10px')
                ->styleAlignCenter()
                , '73%'
            )
        );
        return $Slice;
    }

    /**
     * @param string $PaddingTop
     * @param string $Content
     *
     * @return Slice
     */
    protected function getBsInfo($PaddingTop = '20px', $Content = '')
    {
        $Slice = new Slice();
        $Slice->stylePaddingTop($PaddingTop);
        $Slice->addElement((new Element())
                ->setContent($Content)
                ->styleTextSize('9.5px')
        );
        return $Slice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getTransfer($personId, $MarginTop = '0px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                ->styleTextUnderline()
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                    {% else %}
                        Frau/Herr
                    {% endif %}
                    {% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}')
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , '75%')
        )
            ->styleMarginTop($MarginTop)
            ->styleBorderLeft('0.5px')
            ->styleBorderRight('0.5px')
            ->styleBorderBottom('0.5px');
        return $TransferSlice;
    }
}
