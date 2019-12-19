<?php

namespace Osmianski\Commands;

use OsmScripts\OsmScripts\Commands\CreatePackage;

/** @noinspection PhpUnused */

/**
 * `test` shell command class.
 *
 * @property
 */
class Test extends CreatePackage
{
    #region Properties
    public function default($property) {
        switch ($property) {
            case 'base_package': return 'osmscripts/core';
        }

        return parent::default($property);
    }
    #endregion

    protected function handle() {
        $this->output->writeln($this->version_constraint);
    }
}