<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterCourseContent")
 * @Cache(usage="READ_ONLY")
 */
class TblCourseContent extends Element
{
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_SUBJECT_GROUP = 'serviceTblSubjectGroup';
    const ATTR_DATE = 'Date';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="integer")
     */
    protected int $Lesson;

    /**
     * @Column(type="string")
     */
    protected string $Content;

    /**
     * @Column(type="string")
     */
    protected string $Homework;

    /**
     * @Column(type="string")
     */
    protected string $Remark;

    /**
     * @Column(type="string")
     */
    protected string $Room;

    /**
     * @Column(type="boolean")
     */
    protected $IsDoubleLesson;

    /**
     * @Column(type="datetime")
     */
    protected $DateHeadmaster;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonHeadmaster;

    /**
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {
        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {
        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {
        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubjectGroup
     */
    public function getServiceTblSubjectGroup()
    {
        if (null === $this->serviceTblSubjectGroup) {
            return false;
        } else {
            return Division::useService()->getSubjectGroupById($this->serviceTblSubjectGroup);
        }
    }

    /**
     * @param TblSubjectGroup|null $tblSubjectGroup
     */
    public function setServiceTblSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {
        $this->serviceTblSubjectGroup = ( null === $tblSubjectGroup ? null : $tblSubjectGroup->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {
        $this->Date = $Date;
    }

    /**
     * @return integer
     */
    public function getLesson(): int
    {
        return $this->Lesson;
    }

    /**
     * @return string
     */
    public function getLessonDisplay(): string
    {
        return $this->Lesson === null ? '' : $this->Lesson . '. Unterrichtseinheit';
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * @param integer $Lesson
     */
    public function setLesson(int $Lesson)
    {
        $this->Lesson = $Lesson;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content)
    {
        $this->Content = $Content;
    }

    /**
     * @return string
     */
    public function getHomework(): string
    {
        return $this->Homework;
    }

    /**
     * @param string $Homework
     */
    public function setHomework(string $Homework)
    {
        $this->Homework = $Homework;
    }

    /**
     * @return string
     */
    public function getRoom(): string
    {
        return $this->Room;
    }

    /**
     * @param string $Room
     */
    public function setRoom(string $Room): void
    {
        $this->Room = $Room;
    }

    /**
     * @return bool
     */
    public function getIsDoubleLesson() : bool
    {
        return (bool) $this->IsDoubleLesson;
    }

    /**
     * @param bool $IsDoubleLesson
     */
    public function setIsDoubleLesson(bool $IsDoubleLesson): void
    {
        $this->IsDoubleLesson = $IsDoubleLesson;
    }

    /**
     * @param bool $IsToolTip
     *
     * @return string
     */
    public function getTeacherString(bool $IsToolTip = true): string
    {
        return $this->getServiceTblPerson()
            ? Digital::useService()->getTeacherString($this->getServiceTblPerson(), $IsToolTip)
            : '';
    }

    /**
     * @return string
     */
    public function getRemark(): string
    {
        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark(string $Remark): void
    {
        $this->Remark = $Remark;
    }

    /**
     * @return string
     */
    public function getDateHeadmaster()
    {
        if (null === $this->DateHeadmaster) {
            return false;
        }
        /** @var DateTime $DateHeadmaster */
        $DateHeadmaster = $this->DateHeadmaster;
        if ($DateHeadmaster instanceof DateTime) {
            return $DateHeadmaster->format('d.m.Y');
        } else {
            return (string)$DateHeadmaster;
        }
    }

    /**
     * @param null|DateTime $DateHeadmaster
     */
    public function setDateHeadmaster(DateTime $DateHeadmaster = null)
    {
        $this->DateHeadmaster = $DateHeadmaster;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonHeadmaster()
    {
        if (null === $this->serviceTblPersonHeadmaster) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonHeadmaster);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonHeadmaster(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonHeadmaster = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @param bool $isPrintVersion
     *
     * @return string
     */
    public function getNoticedString(bool $isPrintVersion): string
    {
        if (($date = $this->getDateHeadmaster())) {
            $text = 'am ' . $date . ' durch ' . (($headmaster = $this->getServiceTblPersonHeadmaster()) ? $headmaster->getLastName() : '');

            if ($isPrintVersion) {
                return $text;
            } else {
                return new Success(new Check() . ' ' . $text);
            }
        }

        if ($isPrintVersion) {
            return '';
        } else {
            return new Warning(new Unchecked() . ' noch nicht bestätigt');
        }
    }
}