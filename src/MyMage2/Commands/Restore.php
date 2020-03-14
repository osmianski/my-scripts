<?php

namespace Osmianski\MyMage2\Commands;

use Osmianski\MyMage2\Mage;
use OsmScripts\Core\Command;
use OsmScripts\Core\Script;
use OsmScripts\Core\Shell;
use Symfony\Component\Console\Input\InputOption;

/** @noinspection PhpUnused */

/**
 * `restore` shell command class.
 *
 *
 * @property Mage $mage @required
 * @property Shell $shell @required Helper for running commands in local shell
 * @property string $filename @required
 * @property string $db_name
 */
class Restore extends Command
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'mage': return $script->singleton(Mage::class);
            case 'shell': return $script->singleton(Shell::class);
            case 'filename': return $this->input->getOption('filename');
            case 'db_name': return $this->input->getOption('db');
        }

        return parent::default($property);
    }
    #endregion

    protected function configure() {
        $this
            ->setDescription("Restores Magento2 database")
            ->addOption('filename', null, InputOption::VALUE_OPTIONAL,
                "Backup file name", 'var/db.sql')
            ->addOption('db', null, InputOption::VALUE_OPTIONAL,
                "Database name", $this->mage->db_name);
    }

    protected function handle() {
        $this->output->writeln("Dropping all tables ...");
        $this->dropAllTables();

        $this->output->writeln("Dropping all procedures ...");
        $this->dropAllProcedures();

        $this->shell->run("mysql -h \"{$this->mage->db_host}\" " .
            "-u \"{$this->mage->db_user}\" \"-p{$this->mage->db_password}\" " .
            "\"{$this->db_name}\" < {$this->filename}"
        );
    }

    protected function dropAllTables() {
        $db = new \PDO("mysql:host={$this->mage->db_host};dbname={$this->db_name}",
            $this->mage->db_user, $this->mage->db_password);
        $db->exec('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($db->query('SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'') as $row) {
            $row = (array)$row;
            $table = reset($row);
            $db->exec("DROP TABLE `{$table}`");
        }

        $db->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function dropAllProcedures() {
        $db = new \PDO("mysql:host={$this->mage->db_host};dbname={$this->db_name}",
            $this->mage->db_user, $this->mage->db_password);

        foreach ($db->query("SHOW PROCEDURE STATUS WHERE db = '{$this->db_name}'") as $row) {
            $row = (array)$row;
            $procedure = $row['Name'] ?? $row['name'] ?? $row['NAME'];
            $db->exec("DROP PROCEDURE `{$procedure}`");
        }
    }
}