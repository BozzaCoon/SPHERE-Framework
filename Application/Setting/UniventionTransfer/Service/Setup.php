<?php
namespace SPHERE\Application\Setting\UniventionTransfer\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\UniventionTransfer\Service
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
        $this->setTableUniventionAccount($Schema);
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
    private function setTableUniventionAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblUniventionAccount');

        $this->createColumn($Table, 'Dn', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Firstname', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Lastname', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Record_uid', self::FIELD_TYPE_STRING, true); // record_uid
        $this->createColumn($Table, 'Role', self::FIELD_TYPE_STRING);
        // folgendes wahrscheinlich Json konvertiert speichern
        $this->createColumn($Table, 'RoleUrl', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'School_classes', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'Schools', self::FIELD_TYPE_STRING, true);


        $this->createColumn($Table, 'Workgroups', self::FIELD_TYPE_STRING, true);
        // noch nicht sicher wie Beziehungen aufgezeigt werden, sind aber mehrere Elemente pro Schüler/Sorgeberechtigter also "Semikolon getrennt" oder so
        $this->createColumn($Table, 'Guardians', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'Wards', self::FIELD_TYPE_STRING, true);

//        $this->createColumn($Table, 'Url', self::FIELD_TYPE_STRING);

        // udm_properties
        $this->createColumn($Table, 'Mail', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'MailRecovery', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'DllpServiceAccount', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'DllpDISCH', self::FIELD_TYPE_STRING, true);

        return $Table;
    }
}
