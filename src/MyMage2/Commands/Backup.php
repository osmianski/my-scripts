<?php

namespace Osmianski\MyMage2\Commands;

use Osmianski\MyMage2\Mage;
use OsmScripts\Core\Command;
use OsmScripts\Core\Script;
use OsmScripts\Core\Shell;

/** @noinspection PhpUnused */

/**
 * `backup` shell command class.
 *
 * @property Mage $mage @required
 * @property Shell $shell @required Helper for running commands in local shell
 * @property string $filename @required
 */
class Backup extends Command
{
    #region Properties
    public function __get($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'mage': return $this->mage = $script->singleton(Mage::class);
            case 'shell': return $this->shell = $script->singleton(Shell::class);
            case 'filename': return $this->filename = "{$script->cwd}/var/db.sql";
        }

        return parent::__get($property);
    }
    #endregion

    protected function configure() {
        $this->setDescription("Backs up the database");
    }

    protected function handle() {
        $this->mage->verify();
        $this->mage->verifyInstalled();

        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename), 0777, true);
        }

        $this->shell->run("mysqldump -h \"{$this->mage->db_host}\" " .
            "-u \"{$this->mage->db_user}\" \"-p{$this->mage->db_password}\" " .
            "\"{$this->mage->db_name}\"  > {$this->filename}"
        );
    }
}