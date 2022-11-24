<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Frontend;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiGradeBook extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('changeYear');
        $Dispatcher->registerMethod('changeRole');
        $Dispatcher->registerMethod('loadHeader');

        $Dispatcher->registerMethod('loadViewGradeBookSelect');
        $Dispatcher->registerMethod('loadGradeBookSelectFilterContent');
        $Dispatcher->registerMethod('loadViewGradeBookContent');

        $Dispatcher->registerMethod('loadViewTestEditContent');
        $Dispatcher->registerMethod('saveTestEdit');
        $Dispatcher->registerMethod('loadTestPlanning');

        $Dispatcher->registerMethod('loadViewTestGradeEditContent');
        $Dispatcher->registerMethod('saveTestGradeEdit');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineChangeYear(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChangeYear'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeYear',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function changeYear($Data = null): string
    {
        if (isset($Data["Year"]) && ($tblYear = Term::useService()->getYearById($Data["Year"]))) {
            $gradeBookSelectedYearId = Consumer::useService()->getAccountSettingValue("GradeBookSelectedYearId");
            if (!$gradeBookSelectedYearId || $gradeBookSelectedYearId != $tblYear->getId()) {
                Consumer::useService()->createAccountSetting("GradeBookSelectedYearId", $tblYear->getId());

                return ""
                    . self::pipelineLoadHeader(Frontend::VIEW_GRADE_BOOK_SELECT)
                    . self::pipelineLoadViewGradeBookSelect();
            }
        }

        return "";
    }

    /**
     * @return Pipeline
     */
    public static function pipelineChangeRole(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChangeRole'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeRole',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function changeRole($Data = null): string
    {
        if (isset($Data["IsHeadmaster"])) {
            $role = "Headmaster";
        } elseif (isset($Data["IsAllReadonly"])) {
            $role = "AllReadonly";
        } else {
            $role = "Teacher";
        }

        $gradeBookRole = Consumer::useService()->getAccountSettingValue("GradeBookRole");
        if (!$gradeBookRole || $gradeBookRole != $role) {
            Consumer::useService()->createAccountSetting("GradeBookRole", $role);

            return ""
                . self::pipelineLoadHeader(Frontend::VIEW_GRADE_BOOK_SELECT)
                . self::pipelineLoadViewGradeBookSelect();
        }

        return "";
    }

    /**
     * @param $View
     *
     * @return Pipeline
     */
    public static function pipelineLoadHeader($View): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Header'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadHeader',
        ));
        $ModalEmitter->setPostPayload(array(
            'View' => $View
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $View
     *
     * @return string
     */
    public function loadHeader($View): string
    {
        return Grade::useFrontend()->getHeader($View);
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewGradeBookSelect($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewGradeBookSelect',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadViewGradeBookSelect($Filter): string
    {
        return Grade::useFrontend()->loadViewGradeBookSelect($Filter);
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadGradeBookSelectFilterContent($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'GradeBookSelectFilterContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadGradeBookSelectFilterContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadGradeBookSelectFilterContent($Filter): string
    {
        return Grade::useFrontend()->loadGradeBookSelectFilterContent($Filter);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewGradeBookContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter): string
    {
        return Grade::useFrontend()->loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter);
    }

    /**
     * @param $TestId
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTestEditContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter,
            'TestId' => $TestId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return string
     */
    public function loadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId): string
    {
        return Grade::useFrontend()->loadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTestEdit($DivisionCourseId, $SubjectId, $Filter, $TestId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTestEdit'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter,
            'TestId' => $TestId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     * @param $Data
     *
     * @return string
     */
    public function saveTestEdit($DivisionCourseId, $SubjectId, $Filter, $TestId, $Data): string
    {
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return (new Danger("Fach wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblYear = Grade::useService()->getYear())) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }

        if (($form = Grade::useService()->checkFormTest($Data, $DivisionCourseId, $SubjectId, $Filter, $TestId))) {
            // display Errors on form
            return Grade::useFrontend()->getTestEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TestId);
        }

        $tblGradeType = Grade::useService()->getGradeTypeById($Data['GradeType']);
        $date = $this->getDateTime('Date', $Data);
        $finishDate = $this->getDateTime('FinishDate', $Data);
        $correctionDate = $this->getDateTime('CorrectionDate', $Data);
        $returnDate = $this->getDateTime('ReturnDate', $Data);
        $isContinues = isset($Data['IsContinues']);
        $description = $Data['Description'];

        if (($tblTest = Grade::useService()->getTestById($TestId))) {
            Grade::useService()->updateTest($tblTest, $tblGradeType, $date, $finishDate, $correctionDate, $returnDate, $isContinues, $description);

            $createList = array();
            $removeList = array();
            if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    // löschen
                    if (!isset($Data['DivisionCourses'][$tblDivisionCourse->getId()])) {
                        $removeList[] = Grade::useService()->getTestCourseLinkBy($tblTest, $tblDivisionCourse);
                    }
                }
            } else {
                $tblDivisionCourseList = array();
            }

            // neu
            if (isset($Data['DivisionCourses'])) {
                foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
                    if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))
                        && !isset($tblDivisionCourseList[$divisionCourseId])
                    ) {
                        $createList[] = new TblTestCourseLink($tblTest, $tblDivisionCourse);
                    }
                }
            }

            if (!empty($createList)) {
                Grade::useService()->createEntityListBulk($createList);
            }
            if (!empty($removeList)) {
                Grade::useService()->deleteEntityListBulk($removeList);
            }
        } else {
            if (($tblTestNew = Grade::useService()->createTest(
                $tblYear, $tblSubject, $tblGradeType, $date, $finishDate, $correctionDate, $returnDate, $isContinues, $description
            ))) {
                // Kurse hinzufügen
                if (isset($Data['DivisionCourses'])) {
                    $createList = array();
                    foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
                        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                            $createList[] = new TblTestCourseLink($tblTestNew, $tblDivisionCourse);
                        }
                    }

                    Grade::useService()->createEntityListBulk($createList);
                }
            }
        }

        return new Success("Leistungsüberprüfung wurde erfolgreich gespeichert.")
            . self::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter);
    }

    /**
     * @param string $Identifier
     * @param $Data
     *
     * @return DateTime|null
     */
    private function getDateTime(string $Identifier, $Data): ?DateTime
    {
        if (isset($Data[$Identifier]) && $Data[$Identifier]) {
            return new DateTime($Data[$Identifier]);
        }

        return  null;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadTestPlanning(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TestPlanningContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTestPlanning',
        ));
        $ModalEmitter->setLoadingMessage("Planung wird aktualisiert.");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadTestPlanning($Data = null): string
    {
        return Grade::useFrontend()->loadTestPlanning($Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTestGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTestGradeEditContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter,
            'TestId' => $TestId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return string
     */
    public function loadViewTestGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId): string
    {
        return Grade::useFrontend()->loadViewTestGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTestGradeEdit($DivisionCourseId, $SubjectId, $Filter, $TestId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTestGradeEdit'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter,
            'TestId' => $TestId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     * @param $Data
     *
     * @return string
     */
    public function saveTestGradeEdit($DivisionCourseId, $SubjectId, $Filter, $TestId, $Data): string
    {
        if (!($tblTest = Grade::useService()->getTestById($TestId))) {
            return (new Danger("Leistungsüberprüfung wurde nicht gefunden!", new Exclamation()));
        }

        // todo
//        if (($form = Grade::useService()->checkFormTestGrades($Data, $DivisionCourseId, $SubjectId, $Filter, $TestId))) {
//            // display Errors on form
//            // todo
//            return Grade::useFrontend()->getTestEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TestId);
//        }

        $createList = array();
        $updateList = array();
        $deleteList = array();
        if($Data) {
            $tblTeacher = Account::useService()->getPersonByLogin();
            foreach ($Data as $personId => $item) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $comment = trim($item['Comment']);
                    $publicComment = trim($item['PublicComment']);
                    $grade = str_replace(',', '.', trim($item['Grade']));
                    $isNotAttendance = isset($item['Attendance']);
                    $date = !empty($item['Date']) ? new DateTime($item['Date']) : null;

                    $hasGradeValue = (!empty($grade) && $grade != -1) || $isNotAttendance;
                    $gradeValue = $isNotAttendance ? null : $grade;

                    if (($tblTestGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson))) {
                        if ($hasGradeValue) {
                            $tblTestGrade->setDate($date);
                            $tblTestGrade->setGrade($gradeValue);
                            $tblTestGrade->setComment($comment);
                            $tblTestGrade->setPublicComment($publicComment);
                            $tblTestGrade->setServiceTblPersonTeacher($tblTeacher ?: null);
                            $updateList[] = $tblTestGrade;
                        } else {
                            $deleteList[] = $tblTestGrade;
                        }
                    } else {
                        if ($hasGradeValue) {
                            $createList[] = new TblTestGrade($tblPerson, $tblTest, $date, $gradeValue, $comment, $publicComment, $tblTeacher ?: null);
                        }
                    }
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($updateList)) {
            Grade::useService()->updateEntityListBulk($updateList);
        }
        if (!empty($deleteList)) {
            Grade::useService()->deleteEntityListBulk($deleteList);
        }

        return new Success("Zensuren wurde erfolgreich gespeichert.")
            . self::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter);
    }
}