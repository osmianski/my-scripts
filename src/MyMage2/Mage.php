<?php

namespace Osmianski\MyMage2;

use Exception;
use OsmScripts\Core\Object_;
use OsmScripts\Core\Project;
use OsmScripts\Core\Script;

/**
 * @property Project $project @required
 * @property string $env_filename @required
 * @property array $env @required
 * @property string $db_host @required
 * @property string $db_user @required
 * @property string $db_password @required
 * @property string $db_name @required
 */
class Mage extends Object_
{
    public function __get($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'project': return $this->project = new Project(['path' => $script->cwd]);
            case 'env_filename': return $this->env_filename = "{$script->cwd}/app/etc/env.php";
            case 'env':
                /** @noinspection PhpIncludeInspection */
                return $this->env = include $this->env_filename;
            case 'db_host': return $this->db_host = $this->env['db']['connection']['default']['host'];
            case 'db_user': return $this->db_user = $this->env['db']['connection']['default']['username'];
            case 'db_password': return $this->db_password = $this->env['db']['connection']['default']['password'];
            case 'db_name': return $this->db_name = $this->env['db']['connection']['default']['dbname'];
        }

        return parent::__get($property);
    }

    public function verify() {
        if (!isset($this->project->packages['magento/product-community-edition'])) {
            throw new Exception("'{$this->project->path}' is not Magento 2 project directory");
        }
    }

    public function verifyInstalled() {
        if (!is_file($this->env_filename)) {
            throw new Exception("'{$this->project->path}' is not installed");
        }
    }

    public function verifyManadev() {
        if (!is_dir('manadev-products')) {
            throw new Exception("'manadev-products' directory not found");
        }
    }
}