<?php
namespace SPHERE\Application\People\Person\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Person\Service
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
        $tblSalutation = $this->setTableSalutation($Schema);
        $this->setTablePerson($Schema, $tblSalutation);
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
            (new View($this->getConnection(), 'viewPerson'))
                ->addLink(new TblPerson(), 'tblSalutation', new TblSalutation(''))
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSalutation(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSalutation');
        if (!$this->getConnection()->hasColumn('tblSalutation', 'Salutation')) {
            $Table->addColumn('Salutation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSalutation', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSalutation
     *
     * @return Table
     */
    private function setTablePerson(Schema &$Schema, Table $tblSalutation)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPerson');
        if (!$this->getConnection()->hasColumn('tblPerson', 'Title')) {
            $Table->addColumn('Title', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPerson', 'FirstName')) {
            $Table->addColumn('FirstName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPerson', 'SecondName')) {
            $Table->addColumn('SecondName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPerson', 'LastName')) {
            $Table->addColumn('LastName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPerson', 'BirthName')) {
            $Table->addColumn('BirthName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPerson', 'ImportId')) {
            $Table->addColumn('ImportId', 'string');
        }
        $this->createColumn($Table, 'CallName', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblSalutation, true);

        $this->createIndex($Table,array(TblPerson::ENTITY_REMOVE),false);
        return $Table;
    }
}
