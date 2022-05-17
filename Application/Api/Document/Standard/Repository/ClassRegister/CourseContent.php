<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\ClassRegister;

use DateTime;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class CourseContent extends ClassRegister
{
    private ?TblSubject $tblSubject;
    private ?TblSubjectGroup $tblSubjectGroup;
    private ?TblDivisionSubject $tblDivisionSubject;

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     */
    public function __construct(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup)
    {
        $this->tblDivision = $tblDivision;
        $this->tblSubject = $tblSubject;
        $this->tblSubjectGroup = $tblSubjectGroup;
        $this->name = 'Kursheft';
        $this->displayName = $tblDivision->getDisplayName() . ' ' . $tblSubject->getName() . ' ' . $tblSubjectGroup->getName();

        if (($this->tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblDivision, $tblSubject, $tblSubjectGroup
        ))) {
            $this->tblPersonList = Division::useService()->getStudentByDivisionSubject($this->tblDivisionSubject);
        }
    }

    /**
     * @return string
     */
    private function getCourseType(): string
    {
        return $this->tblSubjectGroup->isAdvancedCourse() ? 'Leistungskurs' : 'Grundkurs';
    }

    /**
     * @return string
     */
    private function getCourseTeachers(): string
    {
        if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherList($this->tblDivision, $this->tblSubject, $this->tblSubjectGroup))) {
            return implode(', ', $tblSubjectTeacherList);
        }

        return '&nbsp;';
    }

    /**
     * @return string
     */
    private function getLevelName(): string
    {
        if (($tblLevel = $this->tblDivision->getTblLevel())) {
            return $tblLevel->getName();
        }

        return '&nbsp;';
    }

    /**
     * @return string
     */
    private function getYearName(): string
    {
        if (($tblYear = $this->tblDivision->getServiceTblYear())) {
            return $tblYear->getName();
        }

        return '&nbsp;';
    }

    /**
     * @return Page[]
     */
    public function getPageList(): array
    {
        $pageList[] = $this->getCoverSheet();
        $pageList[] = new Page();
        $pageList[] = $this->getFirstPage();
        // ist erforderlich für Anzeige der Fehlzeiten Schülernummer
        $pageList[] = $this->getStudentPage(true);
        $pageList[] = $this->getStudentPage(false);

        $this->setCourseContentPageList($pageList);

        return $pageList;
    }

    /**
     * @return Page
     */
    public function getCoverSheet(): Page
    {
        return (new Page())
            ->addSlice((new Slice())
                ->styleMarginTop('100px')
                ->addSection($this->getCoverSection('Schule', $this->tblCompany ? $this->tblCompany->getDisplayName() : ''))
                ->addSection($this->getCoverSection('Fach', $this->tblSubject->getDisplayName()))
                ->addSection($this->getCoverSection('Kursart', $this->getCourseType()))
                ->addSection($this->getCoverSection('Kurslehrer/in', $this->getCourseTeachers()))
                ->addSection($this->getCoverSection('Jahrgangsstufe', $this->getLevelName()))
                ->addSection($this->getCoverSection('Schuljahr', $this->getYearName()))
            )
            ->addSlice((new Slice())
                ->styleMarginTop('250px')
                ->addElement((new Element())
                    ->setContent('Kursheft')
                    ->styleTextSize('70px')
                    ->styleAlignRight()
                    ->stylePaddingRight('70px')
                )
            );
    }

    /**
     * @param string $name
     * @param string $content
     *
     * @return Section
     */
    private function getCoverSection(string $name, string $content): Section
    {
        $textSize = '18px';
        $marginTop = '7px';
        $borderPercentageLeft = '5%';
        $borderPercentageRight = '15%';

        return (new Section())
            ->addElementColumn((new Element()), $borderPercentageLeft)
            ->addElementColumn((new Element())
                ->setContent($name ?: '&nbsp;')
                ->styleMarginTop($marginTop)
                ->styleBorderBottom()
                ->styleTextSize($textSize)
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($content ?: '&nbsp;')
                ->styleMarginTop($marginTop)
                ->styleBorderBottom()
                ->styleTextSize($textSize)
            )
            ->addElementColumn((new Element()), $borderPercentageRight);
    }

    /**
     * @return Page
     */
    private function getFirstPage(): Page
    {
        if ($this->tblCompany && ($tblAddress = $this->tblCompany->fetchMainAddress())) {
            $address = $tblAddress->getGuiTwoRowString();
        } else {
            $address = '&nbsp;';
        }

        $textSize = '10px';
        $paddingLeft = '5px';
        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->styleHeight('122px')
                        ->styleBorderTop()
                        ->styleBorderBottom()
                        ->styleBorderLeft()
                        ->addElement((new Element())
                            ->setContent('Name und Ort der Schule')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->tblCompany ? $this->tblCompany->getDisplayName() : '&nbsp;')
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($address)
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                        , '50%')
                    ->addSliceColumn((new Slice())
                        ->styleHeight('122px')
                        ->styleBorderAll()
                        ->addElement((new Element())
                            ->setContent('Schuljahr')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->getYearName())
                            ->styleMarginTop('10px')
                            ->stylePaddingBottom('10px')
                            ->stylePaddingLeft($paddingLeft)
                            ->styleBorderBottom()
                        )
                        ->addElement((new Element())
                            ->setContent('Jahrgangsstufe')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->getLevelName())
                            ->styleMarginTop('10px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                        , '50%')
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('40px')
                ->styleBorderLeft()
                ->styleBorderBottom()
                ->addSection($this->getFirstPageSection('Kurslehrer/in', $this->getCourseTeachers()))
                ->addSection($this->getFirstPageSection('Fach', $this->tblSubject->getDisplayName()))
                ->addSection($this->getFirstPageSection('Kursart', $this->getCourseType()))
            );
    }

    /**
     * @param string $name
     * @param string $content
     *
     * @return Section
     */
    private function getFirstPageSection(string $name, string $content): Section
    {
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleAlignCenter()
                ->stylePaddingTop('10px')
                ->stylePaddingBottom('10px')
                ->styleBackgroundColor('#CCC')
                ->styleBorderTop()
                ->styleBorderRight()
                , '15%')
            ->addElementColumn((new Element())
                ->setContent($content)
                ->stylePaddingLeft('10px')
                ->stylePaddingTop('10px')
                ->stylePaddingBottom('10px')
                ->styleBorderTop()
                ->styleBorderRight()
            );
    }

    /**
     * @param array $pageList
     */
    private function setCourseContentPageList(array &$pageList)
    {
        $count = 0;
        $noticed = '';
        $sliceList = array();
        $divisionList = array('0' => $this->tblDivision);
        if (($tblCourseContentList = Digital::useService()->getCourseContentListBy($this->tblDivision, $this->tblSubject, $this->tblSubjectGroup))) {
            $tblCourseContentList = (new Extension())->getSorter($tblCourseContentList)->sortObjectBy(TblCourseContent::ATTR_DATE, new DateTimeSorter());
            foreach ($tblCourseContentList as $tblCourseContent) {
                $personNumberList = array();
                $lessonArray = array();
                $lesson = $tblCourseContent->getLesson();
                $lessonArray[$lesson] = $lesson;
                if ($tblCourseContent->getIsDoubleLesson()) {
                    $lesson++;
                    $lessonArray[$lesson] = $lesson;
                }

                if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(new DateTime($tblCourseContent->getDate()),
                    null, null, $divisionList, array(), $hasTypeOption, null))
                ) {
                    foreach ($AbsenceList as $Absence) {
                        if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                            $isAdd = false;
                            if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                                foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                                    if (isset($lessonArray[$tblAbsenceLesson->getLesson()])) {
                                        $isAdd = true;
                                        break;
                                    }
                                }
                                // ganztägig
                            } else {
                                $isAdd = true;
                            }

                            if ($isAdd && (($tblPerson = $tblAbsence->getServiceTblPerson()))) {
                                if (isset($this->personNumberAbsenceList[$tblPerson->getId()])) {
                                    $personNumberList[] = $this->personNumberAbsenceList[$tblPerson->getId()];
                                }
                            }
                        }
                    }
                }

                $count++;
                $sliceList[] = $this->getCourseContentSlice($tblCourseContent, $personNumberList ? implode(', ', $personNumberList) : '');

                if (($temp = $tblCourseContent->getNoticedString(true))) {
                    $noticed = $temp;
                }

                // Neue Seite
                if ($count == 8) {
                    $count = 0;
                    $pageList[] = (new Page())
                        ->addSliceArray($this->getCourseContentHeaderSliceList())
                        ->addSliceArray($sliceList)
                        ->addSlice($this->getSignSlice($noticed));
                    $sliceList = array();
                    $noticed = '';
                }
            }
        }

        // Letzte Seite
        if (!empty($sliceList)) {
            $pageList[] = (new Page())
                ->addSliceArray($this->getCourseContentHeaderSliceList())
                ->addSliceArray($sliceList)
                ->addSlice($this->getSignSlice($noticed));
        }
    }

    /**
     * @return array
     */
    private function getCourseContentHeaderSliceList(): array
    {
        $width[1] = '12%';
        $width[2] = '3%';
        $width[3] = '32%';
        $width[4] = '20%';
        $width[5] = '5%';
        $width[6] = '20%';
        $width[7] = '8%';

        $sliceList[] = (new Slice())->addElement((new Element())->setContent('Kursprotokolle')->styleTextBold())->styleMarginBottom('5px');
        $sliceList[] = (new Slice())
            ->styleBorderLeft()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBackgroundColor('#CCC')
            ->addSection((new Section())
                ->addElementColumn($this->getHeaderElement('Datum'), $width[1])
                ->addElementColumn((new Element())
                    ->setContent($this->setRotatedContent('Anzahl UEs', '-27px', '-40px', '-12px'))
                    ->styleBackgroundColor('#CCC')
                    , $width[2])
                ->addElementColumn($this->getHeaderElement('Thema der Stunde')->styleBorderLeft(), $width[3])
                ->addElementColumn($this->getHeaderElement('Hausaufgabe'), $width[4])
                ->addElementColumn((new Element())
                    ->setContent($this->setRotatedContent('Fehlende SuS', '-27px', '-40px', '-15px'))
                    ->styleBackgroundColor('#CCC')
                    , $width[5])
                ->addElementColumn($this->getHeaderElement('Bemerkungen')->styleBorderLeft(), $width[6])
                ->addElementColumn($this->getHeaderElement('Sign.'), $width[7])
            );

        return $sliceList;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     * @param string $missingString
     *
     * @return Slice
     */
    private function getCourseContentSlice(TblCourseContent $tblCourseContent, string $missingString): Slice
    {
        $width[1] = '12%';
        $width[2] = '3%';
        $width[3] = '32%';
        $width[4] = '20%';
        $width[5] = '5%';
        $width[6] = '20%';
        $width[7] = '8%';

        return (new Slice())
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn($this->getElement($tblCourseContent->getDate(), true), $width[1])
                ->addElementColumn($this->getElement($tblCourseContent->getIsDoubleLesson() ? '2' : '1', true), $width[2])
                ->addElementColumn($this->getElement($tblCourseContent->getContent()), $width[3])
                ->addElementColumn($this->getElement($tblCourseContent->getHomework()), $width[4])
                ->addElementColumn($this->getElement($missingString), $width[5])
                ->addElementColumn($this->getElement($tblCourseContent->getRemark()), $width[6])
                ->addElementColumn($this->getElement($tblCourseContent->getTeacherString(), true), $width[7])
            );
    }

    /**
     * @param string $noticed
     *
     * @return Slice
     */
    private function getSignSlice(string $noticed): Slice
    {
        return (new Slice())
            ->styleMarginTop('30px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Kenntnis genommmen:')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent($noticed ?: '&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    , '75%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('Datum und Schulleiterin / Schulleiter')
                    ->styleTextSize('11px')
                    ->styleAlignCenter()
                    , '75%')
            );
    }

    /**
     * @param string $name
     * @param bool $isCenter
     *
     * @return Element
     */
    private function getElement(string $name, bool $isCenter = false): Element
    {
        if ($isCenter) {
            return (new Element())
                ->setContent($name !== '' ? $name : '&nbsp;')
                ->styleAlignCenter()
                ->styleHeight('55px')
                ->styleBorderBottom()
                ->styleBorderRight()
                ->stylePaddingTop('40px')
                ->stylePaddingBottom('5px');
        }

        return (new Element())
            ->setContent($name !== '' ? $name : '&nbsp;')
            ->styleHeight('90px')
            ->styleBorderBottom()
            ->styleBorderRight()
            ->stylePaddingLeft('3px')
            ->stylePaddingTop('5px')
            ->stylePaddingBottom('5px');
    }

    /**
     * @param string $name
     *
     * @return Element
     */
    private function getHeaderElement(string $name): Element
    {
        return (new Element())
            ->setContent($name)
            ->styleAlignCenter()
            ->stylePaddingTop('40px')
            ->stylePaddingBottom('40px')
            ->styleBorderRight();
    }
}