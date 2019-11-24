<?php

namespace Osmianski\Deploy\Commands;

use OsmScripts\Core\Command;
use OsmScripts\Core\Script;
use OsmScripts\Core\Shell;

/** @noinspection PhpUnused */

/**
 * `beta.osmdocs.com` shell command class.
 *
 * @property Shell $shell @required Helper for running commands in local shell
 * @property string $path Normal project path
 * @property string $root_path Path of root user's project
 */
class BetaOsmdocsCom extends Command
{
    public $project = 'beta.osmdocs.com';
    public $user = 'vagrant';

    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'shell': return $script->singleton(Shell::class);
            case 'path': return "/projects/{$this->project}";
            case 'root_path': return "/projects/root.{$this->project}";
        }

        return parent::default($property);
    }
    #endregion

    protected function configure() {
        $this->setDescription("Deploys the latest version to '{$this->project}''");
    }

    protected function handle() {
        $this->shell->run("supervisorctl stop {$this->project}__queue:* {$this->project}__watch_data:*");

        $this->shell->su($this->user, function() {
            $this->shell->cd($this->path, function() {
                $this->shell->run("git pull");
                $this->shell->run("composer update");
            });
        });

        $this->shell->cd($this->root_path, function() {
            $this->shell->run("git pull");
            $this->shell->run("composer update");
        });

        $this->shell->run("supervisorctl reread");
        $this->shell->run("supervisorctl update {$this->project}__queue {$this->project}__watch_data");
        $this->shell->run("supervisorctl start {$this->project}__queue:* {$this->project}__watch_data:*");
    }
}