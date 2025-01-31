<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HGGT;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymHjInfo extends Style
{
    /**
     * @return array
     */
    public function selectValuesTransfer(): array
    {
        return array(
            1 => "",
            2 => "ist versetzungsgefährdet"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page|Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getCustomHeader($this->isSample(), 'Halbjahresinformation'))
            ->addSlice($this->getCustomDivisionAndYear($personId, 'Schulhalbjahr'))
            ->addSlice($this->getCustomRatingContent($personId))
            ->addSlice($this->getCustomGradeLanes($personId))
            ->addSlice($this->getCustomSubjectLanes($personId))
            ->addSlice($this->getCustomRemark($personId))
            ->addSlice($this->getCustomMissing($personId))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId, false))
            ->addSlice($this->getCustomParentSign());
    }
}