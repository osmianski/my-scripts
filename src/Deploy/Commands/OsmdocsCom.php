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
 * @property string $project Project directory name
 * @property string $user Linux user, project directory owner
 * @property string $path Normal project path
 * @property string $root_path Path of root user's project
 */
class OsmdocsCom extends Command
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'shell': return $script->singleton(Shell::class);
            case 'project': return $this->getName();
            case 'user': return 'vo';
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
                $this->shell->run("git reset --hard HEAD");
                $this->shell->run("git pull origin v1");
                $this->shell->run("composer install --no-scripts");
                $this->shell->run("php run config:npm");
                $this->shell->run("npm install");
                $this->shell->run("npm run webpack");
            });
        });

        $this->shell->cd($this->root_path, function() {
            $this->shell->run("git reset --hard HEAD");
            $this->shell->run("git pull origin v1");
            $this->shell->run("composer install --no-scripts");
            $this->shell->run("php fresh");
        });

        $this->shell->run("supervisorctl reread");
        $this->shell->run("supervisorctl update {$this->project}__queue {$this->project}__watch_data");
        $this->shell->run("supervisorctl start {$this->project}__queue:* {$this->project}__watch_data:*");
    }
}