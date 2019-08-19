<?php

namespace Osmianski\MyMage2\Commands;

use Osmianski\MyMage2\Links;
use OsmScripts\Core\Command;
use OsmScripts\Core\Files;
use OsmScripts\Core\Script;

/** @noinspection PhpUnused */

/**
 * `delete:links` shell command class.
 *
 * @property Links $links @required
 * @property Files $files @required Helper for generating files.
 */
class DeleteLinks extends Command
{
    #region Properties
    public function __get($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'links': return $this->links = $script->singleton(Links::class);
            case 'files': return $this->files = $script->singleton(Files::class);
        }

        return null;
    }
    #endregion

    protected function handle() {
        foreach (array_reverse(array_keys($this->links->get())) as $link) {
            $this->files->deleteLink($link);
        }
    }
}