<?php

namespace SPHERE\Application\Reporting\SerialLetter\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

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
        $tblFilterCategory = $this->setTableFilterCategory($Schema);
        $tblSerialLetter = $this->setTableSerialLetter($Schema, $tblFilterCategory);
        $this->setTableFilterField($Schema, $tblFilterCategory, $tblSerialLetter);
        $this->setTableSerialPerson($Schema, $tblSerialLetter);
        $this->setTableSerialCompany($Schema, $tblSerialLetter);
        $this->setTableAddressPerson($Schema, $tblSerialLetter);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableFilterCategory($Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFilterCategory');
        if (!$this->getConnection()->hasColumn('tblFilterCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblFilterCategory
     *
     * @return Table
     */
    private function setTableSerialLetter(Schema &$Schema, $tblFilterCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSerialLetter');
        if (!$this->getConnection()->hasColumn('tblSerialLetter', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSerialLetter', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblFilterCategory, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblFilterCategory
     * @param Table  $tblSerialLetter
     *
     * @return Table
     */
    private function setTableFilterField($Schema, $tblFilterCategory, $tblSerialLetter)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFilterField');
        if (!$this->getConnection()->hasColumn('tblFilterField', 'Field')) {
            $Table->addColumn('Field', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFilterField', 'Value')) {
            $Table->addColumn('Value', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFilterField', 'FilterNumber')) {
            $Table->addColumn('FilterNumber', 'integer');
        }
        $this->getConnection()->addForeignKey($Table, $tblFilterCategory, false);
        $this->getConnection()->addForeignKey($Table, $tblSerialLetter, false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSerialLetter
     *
     * @return Table
     */
    private function setTableSerialPerson(Schema $Schema, Table $tblSerialLetter)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSerialPerson');
        if (!$this->getConnection()->hasColumn('tblSerialPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblSerialLetter, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSerialLetter
     *
     * @return Table
     */
    private function setTableSerialCompany(Schema $Schema, Table $tblSerialLetter)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSerialCompany');
        if (!$this->getConnection()->hasColumn('tblSerialCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblSerialCompany', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblSerialCompany', 'isIgnore')) {
            $Table->addColumn('isIgnore', 'boolean');
        }
        $this->getConnection()->addForeignKey($Table, $tblSerialLetter, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSerialLetter
     *
     * @return Table
     */
    private function setTableAddressPerson(Schema &$Schema, Table $tblSerialLetter)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAddressPerson');
        if (!$this->getConnection()->hasColumn('tblAddressPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblAddressPerson', 'serviceTblToPerson')) {
            $Table->addColumn('serviceTblToPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblToPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblToPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblToPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblAddressPerson', 'serviceTblPersonToAddress')) {
            $Table->addColumn('serviceTblPersonToAddress', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPersonToAddress'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPersonToAddress', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPersonToAddress', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblSerialLetter, true);

        return $Table;
    }
}
