<?php

namespace Osmianski\MyMage2;

use Exception;
use OsmScripts\Core\Object_;
use OsmScripts\Core\Project;
use OsmScripts\Core\Script;

/**
 * @property Project $project @required
 */
class Mage extends Object_
{
    public function __get($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'project': return $this->project = new Project(['path' => $script->cwd]);
        }

        return parent::__get($property);
    }

    public function verify() {
        if (!isset($this->project->packages['magento/product-community-edition'])) {
            throw new Exception("'{$this->project->path}' is not Magento 2 project directory");
        }
    }

    public function verifyManadev() {
        if (!is_dir('manadev-products')) {
            throw new Exception("'manadev-products' directory not found");
        }
    }
}