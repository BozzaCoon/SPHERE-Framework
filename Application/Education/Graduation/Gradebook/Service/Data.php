<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:31
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {


    }

    /**
     * @param $Name
     * @param $Code
     * @param $Description
     * @param $IsHighlighted
     * @return null|TblGradeType
     */
    public function createGradeType($Name, $Code, $Description, $IsHighlighted)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblGradeType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setIsHighlighted($IsHighlighted);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblGradeType $tblGradeType
     * @param $Grade
     * @param string $Comment
     * @return null|object|TblGrade
     */
    public function createGrade(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblGradeType $tblGradeType,
        $Grade,
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();

//        $Entity = $Manager->getEntity('TblGrade')
//            ->findOneBy(array(
//                TblGrade::ATTR_DATE => $Date,
//                TblGrade::ATTR_SERVICE_TBL_PERIOD => $tblPeriod,
//                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson,
//                TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject,
//                TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType
//            ));

//        if (null === $Entity) {
        $Entity = new TblGrade();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setGrade($Grade);
        $Entity->setComment($Comment);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
//        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeType', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')->findAll();
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @return TblGrade[]|bool
     */
    public function getGradesByStudentAndSubjectAndPeriod(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod
    ) {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGrade')
            ->findBy(array(
                    TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblGrade::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId(),
                )
            );

        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblGrade $tblGrade
     * @param $Grade
     * @param string $Comment
     * @return bool
     */
    public function updateGrade(
        TblGrade $tblGrade,
        $Grade,
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGrade $Entity */
        $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());

        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     * @return bool|TblGrade
     */
    public function getGradeById($Id)
    {

//        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', $Id);
//        Debugger::screenDump($EntityManager);
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGrade', $Id);
//        $Entity = $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $Id
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTest', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblTest[]
     */
    public function getTestAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTest')->findAll();
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblGradeType $tblGradeType
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     *
     * @return TblTest
     */
    public function createTest(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblGradeType $tblGradeType,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTest();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setDescription($Description);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
        $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     * @return bool
     */
    public function updateTest(
        TblTest $tblTest,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
            $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param string $Grade
     * @param string $Comment
     * @return null|TblGrade
     */
    public function createGradeToTest(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $Grade = '',
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGrade')
            ->findOneBy(array(
                TblGrade::ATTR_TBL_TEST => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblGrade();
            $Entity->setTblTest($tblTest);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblTest->getServiceTblDivision());
            $Entity->setServiceTblSubject($tblTest->getServiceTblSubject());
            $Entity->setServiceTblPeriod($tblTest->getServiceTblPeriod());
            $Entity->setTblGradeType($tblTest->getTblGradeType());
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblGrade')->findBy(array(
            TblGrade::ATTR_TBL_TEST => $tblTest->getId()
        ));

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param $Id
     * @return bool|TblScoreGroup
     */
    public function getScoreGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreGroup', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreCondition', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreRule', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreRuleConditionList', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreConditionGradeTypeList', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreConditionGroupList', $Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreGroupGradeTypeList', $Id);
    }
}