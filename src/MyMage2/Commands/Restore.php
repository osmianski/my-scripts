<?php

namespace Osmianski\MyMage2\Commands;

use Osmianski\MyMage2\Mage;
use OsmScripts\Core\Command;
use OsmScripts\Core\Script;
use OsmScripts\Core\Shell;

/** @noinspection PhpUnused */

/**
 * `restore` shell command class.
 *
 *
 * @property Mage $mage @required
 * @property Shell $shell @required Helper for running commands in local shell
 * @property string $filename @required
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
            case 'filename': return "{$script->cwd}/var/db.sql";
        }

        return parent::default($property);
    }
    #endregion

    protected function configure() {
        // TODO: describe the command usage, arguments and options
    }

    protected function handle() {
        $this->output->writeln("Dropping all tables ...");
        $this->dropAllTables();

        $this->shell->run("mysql -h \"{$this->mage->db_host}\" " .
            "-u \"{$this->mage->db_user}\" \"-p{$this->mage->db_password}\" " .
            "\"{$this->mage->db_name}\" < {$this->filename}"
        );
    }

    protected function dropAllTables() {
        $db = new \PDO("mysql:host={$this->mage->db_host};dbname={$this->mage->db_name}",
            $this->mage->db_user, $this->mage->db_password);
        $db->exec('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($db->query('SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'') as $row) {
            $row = (array)$row;
            $table = reset($row);
            $db->exec("DROP TABLE `{$table}`");
        }

        $db->exec('SET FOREIGN_KEY_CHECKS=1;');
    }
}