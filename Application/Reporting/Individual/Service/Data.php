<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    public function getView()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'viewStudent');
    }

}
