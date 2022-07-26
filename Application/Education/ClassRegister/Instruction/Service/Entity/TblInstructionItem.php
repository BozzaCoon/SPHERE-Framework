<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterInstructionItem")
 * @Cache(usage="READ_ONLY")
 */
class TblInstructionItem extends Element
{
    const ATTR_TBL_INSTRUCTION = 'tblClassRegisterInstruction';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_GROUP = 'serviceTblGroup';
    const ATTR_SERVICE_TBL_DIVISION_SUBJECT = 'serviceTblDivisionSubject';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_DATE = 'Date';
    const ATTR_IS_MAIN = 'IsMain';

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterInstruction;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="string")
     */
    protected string $Subject;

    /**
     * @Column(type="string")
     */
    protected string $Content;

    /**
     * @Column(type="boolean")
     */
    protected $IsMain;

    /**
     * @return bool|TblInstruction
     */
    public function getTblInstruction()
    {
        if (null === $this->tblClassRegisterInstruction) {
            return false;
        } else {
            return Instruction::useService()->getInstructionById($this->tblClassRegisterInstruction);
        }
    }

    /**
     * @param TblInstruction|null $tblInstruction
     */
    public function setTblInstruction(TblInstruction $tblInstruction = null)
    {
        $this->tblClassRegisterInstruction = (null === $tblInstruction ? null : $tblInstruction->getId());
    }

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
     * @return bool|TblGroup
     */
    public function getServiceTblGroup()
    {
        if(null === $this->serviceTblGroup){
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroup);
        }
    }

    /**
     * @param null|TblGroup $serviceTblGroup
     */
    public function setServiceTblGroup(TblGroup $serviceTblGroup = null)
    {
        $this->serviceTblGroup = (null === $serviceTblGroup ? null : $serviceTblGroup->getId());
    }

    /**
     * @return bool|TblDivisionSubject
     */
    public function getServiceTblDivisionSubject()
    {
        if (null === $this->serviceTblDivisionSubject) {
            return false;
        } else {
            return Division::useService()->getDivisionSubjectById($this->serviceTblDivisionSubject);
        }
    }

    /**
     * @param TblDivisionSubject|null $tblDivisionSubject
     */
    public function setServiceTblDivisionSubject(TblDivisionSubject $tblDivisionSubject = null)
    {
        $this->serviceTblDivisionSubject = (null === $tblDivisionSubject ? null : $tblDivisionSubject->getId());
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {
        $this->serviceTblYear = ( null === $tblYear ? null : $tblYear->getId() );
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
     * @return string
     */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /**
     * @param string $Subject
     */
    public function setSubject(string $Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content)
    {
        $this->Content = $Content;
    }

    /**
     * @return bool
     */
    public function getIsMain()
    {
        return $this->IsMain;
    }

    /**
     * @param bool $IsMain
     */
    public function setIsMain($IsMain): void
    {
        $this->IsMain = (bool) $IsMain;
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
}