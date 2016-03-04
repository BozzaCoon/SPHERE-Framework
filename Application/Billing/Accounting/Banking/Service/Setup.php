<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Banking\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();

        $tblDebtor = $this->setTableDebtor($Schema);
        $tblBankAccount = $this->setTableBankAccount($Schema);
        $tblBankReference = $this->setTableBankReference($Schema);
        $this->setTableDebtorSelection($Schema, $tblDebtor, $tblBankAccount, $tblBankReference);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDebtor(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtor');
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBankAccount');
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'BankName')) {
            $Table->addColumn('BankName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'IBAN')) {
            $Table->addColumn('IBAN', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'BIC')) {
            $Table->addColumn('BIC', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'Owner')) {
            $Table->addColumn('Owner', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'CashSign')) {
            $Table->addColumn('CashSign', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankReference(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBankReference');

        if (!$this->getConnection()->hasColumn('tblBankReference', 'Reference')) {
            $Table->addColumn('Reference', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankReference', 'ReferenceDate')) {
            $Table->addColumn('ReferenceDate', 'date', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBankReference', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDebtor
     * @param Table  $tblBankAccount
     * @param Table  $tblBankReference
     *
     * @return Table
     */
    private function setTableDebtorSelection(Schema &$Schema, Table $tblDebtor, Table $tblBankAccount, Table $tblBankReference)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtorSelection');

        if (!$this->getConnection()->hasColumn('tblDebtorSelection', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblDebtorSelection', 'serviceTblPersonPayers')) {
            $Table->addColumn('serviceTblPersonPayers', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblDebtorSelection', 'serviceTblItem')) {
            $Table->addColumn('serviceTblItem', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblDebtorSelection', 'serviceTblPaymentType')) {
            $Table->addColumn('serviceTblPaymentType', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblDebtor, true);
        $this->getConnection()->addForeignKey($Table, $tblBankAccount, true);
        $this->getConnection()->addForeignKey($Table, $tblBankReference, true);

        return $Table;
    }
}
