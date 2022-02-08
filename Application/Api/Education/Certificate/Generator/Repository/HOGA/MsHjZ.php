<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class MsHjZ extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school[] = 'Oberschule der';
        $school[] = 'HOGA Schloss Albrechtsberg g SchulgmbH';
        $school[] = 'Staatlich anerkannte Schule in freier Trägerschaft';

        $title = 'Halbjahreszeugnis der Oberschule';

        return (new Page())
            ->addSlice($this->getHeader($school, $title))
            ->addSlice($this->getDivisionYearStudent($personId, '1. Schulhalbjahr:'))
            ->addSlice($this->getCustomCourse($personId))
            ->addSlice($this->getCustomGradeLanes($personId, '3px'))
            ->addSlice($this->getSliceSpace('65px'))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array(), false, true)->styleHeight('310px'))
            ->addSlice($this->getCustomElective($personId))
            ->addSlice($this->getCustomRemark($personId, '16px', '80px'))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo());
    }
}