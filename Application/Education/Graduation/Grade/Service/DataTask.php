<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGradeTypeLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataTask extends DataMigrate
{
    /**
     * @param $id
     *
     * @return false|TblTask
     */
    public function getTaskById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblTask', $id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblTask[]
     */
    public function getTaskListByYear(TblYear $tblYear)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTask', array(TblTask::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return TblGradeType[]|false
     */
    public function getGradeTypeListByTask(TblTask $tblTask)
    {
        $resultList = array();
        if (($list = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskGradeTypeLink',
            array(TblTaskGradeTypeLink::ATTR_TBL_TASK => $tblTask->getId()))
        )) {
            /** @var TblTaskGradeTypeLink $item */
            foreach ($list as $item) {
                if (($tblGradeType = $item->getTblGradeType())) {
                    $resultList[$tblGradeType->getId()] = $tblGradeType;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblTaskGradeTypeLink
     */
    public function getTaskGradeTypeLinkBy(TblTask $tblTask, TblGradeType $tblGradeType)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTaskGradeTypeLink', array(
            TblTaskGradeTypeLink::ATTR_TBL_TASK => $tblTask->getId(),
            TblTaskGradeTypeLink::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return false|TblTaskGradeTypeLink[]
     */
    public function getTaskGradeTypeLinkListBy(TblTask $tblTask)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskGradeTypeLink', array(
            TblTaskGradeTypeLink::ATTR_TBL_TASK => $tblTask->getId()
        ));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByTask(TblTask $tblTask)
    {
        $resultList = array();
        if (($list = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskCourseLink',
            array(TblTaskCourseLink::ATTR_TBL_TASK => $tblTask->getId()))
        )) {
            /** @var TblTaskCourseLink $item */
            foreach ($list as $item) {
                if (($tblDivisionCourse = $item->getServiceTblDivisionCourse())) {
                    $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTaskCourseLink
     */
    public function getTaskCourseLinkBy(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTaskCourseLink', array(
            TblTaskCourseLink::ATTR_TBL_TASK => $tblTask->getId(),
            TblTaskCourseLink::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
        ));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return false|TblTaskCourseLink[]
     */
    public function getTaskCourseLinkListByTask(TblTask $tblTask)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskCourseLink', array(
            TblTaskCourseLink::ATTR_TBL_TASK => $tblTask->getId()
        ));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return false|TblTaskGrade[]
     */
    public function getTaskGradeListByTask(TblTask $tblTask)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskGrade', array(TblTaskGrade::ATTR_TBL_TASK => $tblTask->getId()));
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function getHasTaskGradesByTask(TblTask $tblTask): bool
    {
        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTaskGrade', array(TblTaskGrade::ATTR_TBL_TASK => $tblTask->getId()))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblPerson $tblPerson
     *
     * @return false|TblTaskGrade[]
     */
    public function getTaskGradeListByTaskAndPerson(TblTask $tblTask, TblPerson $tblPerson)
    {
        $parameters[TblTaskGrade::ATTR_TBL_TASK] = $tblTask->getId();
        $parameters[TblTaskGrade::ATTR_SERVICE_TBL_PERSON] = $tblPerson->getId();

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTaskGrade', $parameters);
    }

    /**
     * @param TblYear $tblYear
     * @param bool $IsTypeBehavior
     * @param string $Name
     * @param DateTime|null $Date
     * @param DateTime|null $FromDate
     * @param DateTime|null $ToDate
     * @param bool $IsAllYears
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblTask
     */
    public function createTask(TblYear $tblYear, bool $IsTypeBehavior, string $Name, ?DateTime $Date, ?DateTime $FromDate, ?DateTime $ToDate,
        bool  $IsAllYears, ?TblScoreType $tblScoreType): TblTask
    {
        $Manager = $this->getEntityManager();

        $Entity = new TblTask($tblYear, $IsTypeBehavior, $Name, $Date, $FromDate, $ToDate, $IsAllYears, $tblScoreType);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTask $tblTask
     * @param string $Name
     * @param DateTime|null $Date
     * @param DateTime|null $FromDate
     * @param DateTime|null $ToDate
     * @param TblScoreType|null $tblScoreType
     * @param bool $IsAllYears
     *
     * @return bool
     */
    public function updateTask(TblTask $tblTask, string $Name, ?DateTime $Date, ?DateTime $FromDate, ?DateTime $ToDate,
        bool  $IsAllYears, ?TblScoreType $tblScoreType): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblTask $Entity */
        $Entity = $Manager->getEntityById('TblTask', $tblTask->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDate($Date);
            $Entity->setFromDate($FromDate);
            $Entity->setToDate($ToDate);
            $Entity->setTblScoreType($tblScoreType);
            $Entity->setIsAllYears($IsAllYears);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}