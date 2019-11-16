<?php

namespace Osmianski\Mymage2\Commands;

use Osmianski\MyMage2\Links;
use Osmianski\MyMage2\Mage;
use OsmScripts\Core\Command;
use OsmScripts\Core\Files;
use OsmScripts\Core\Script;

/** @noinspection PhpUnused */

/**
 * `create:links` shell command class.
 *
 * @property Links $links @required
 * @property Files $files @required Helper for generating files.
 * @property Mage $mage @required
 */
class CreateLinks extends Command
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'links': return $script->singleton(Links::class);
            case 'files': return $script->singleton(Files::class);
            case 'mage': return $script->singleton(Mage::class);
        }

        return parent::default($property);
    }
    #endregion

    protected function handle() {
        $this->mage->verify();
        $this->mage->verifyManadev();

        foreach($this->links->get() as $link => $target) {
            $this->files->createLink($target, $link);
        }
    }
}