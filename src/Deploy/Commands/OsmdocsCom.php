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
 *
 * @property string[] $services
 * @property string $services_
 * @property string $services__
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
            case 'services': return [ 'default_queue', 'privileged_queue', 'watch_data'];
            case 'services_': return implode(' ', array_map(function($service) {
                return "{$this->project}__{$service}";
            }, $this->services));
            case 'services__': return implode(' ', array_map(function($service) {
                return "{$this->project}__{$service}:*";
            }, $this->services));
        }

        return parent::default($property);
    }
    #endregion

    protected function configure() {
        $this->setDescription("Deploys the latest version to '{$this->project}''");
    }

    protected function handle() {
        $this->shell->run("supervisorctl stop {$this->services__}");

        $this->shell->su($this->user, function() {
            $this->shell->cd($this->path, function() {
                $this->shell->run("git reset --hard HEAD");
                $this->shell->run("git pull origin v1");
                $this->shell->run("composer update --no-scripts");
                $this->shell->run("php run migrations");
                $this->shell->run("php run config:npm");
                $this->shell->run("npm install");
                $this->shell->run("npm run webpack");
            });
        });

        $this->shell->cd($this->root_path, function() {
            $this->shell->run("git reset --hard HEAD");
            $this->shell->run("git pull origin v1");
            $this->shell->run("composer update --no-scripts");
            $this->shell->run("php fresh");
        });

        $this->shell->run("supervisorctl reread");
        $this->shell->run("supervisorctl update {$this->services_}");
        $this->shell->run("supervisorctl start {$this->services__}");
    }
}