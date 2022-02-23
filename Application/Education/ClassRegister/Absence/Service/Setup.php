<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:58
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {
        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblAbsence = $this->setTableAbsence($Schema);
        $this->setTableAbsenceLesson($Schema, $tblAbsence);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewAbsence') )
                ->addLink(new TblAbsence(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableAbsence(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAbsence');
        if (!$this->getConnection()->hasColumn('tblAbsence', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'FromDate')) {
            $Table->addColumn('FromDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'ToDate')) {
            $Table->addColumn('ToDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'Remark')) {
            $Table->addColumn('Remark', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'Status')) {
            $Table->addColumn('Status', 'smallint');
        }

        $this->createColumn($Table, 'Type', self::FIELD_TYPE_SMALLINT, false, 0);
        $this->createColumn($Table, 'serviceTblPersonStaff', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'IsCertificateRelevant', self::FIELD_TYPE_BOOLEAN, false, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblAbsence
     *
     * @return Table
     */
    private function setTableAbsenceLesson(Schema &$Schema, Table $tblAbsence)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblAbsenceLesson');
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);

        $this->getConnection()->addForeignKey($Table, $tblAbsence);

        return $Table;
    }
}
