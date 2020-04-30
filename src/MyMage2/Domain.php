<?php

namespace Osmianski\MyMage2;

use OsmScripts\Core\Object_;

/**
 * Properties required during object creation
 *
 * @property string $name
 *
 * Calculated properties
 *
 * @property string $filename
 * @property string $link
 */
class Domain extends Object_
{
    public function default($property) {
        switch ($property) {
            case 'filename': return "/etc/nginx/sites-available/{$this->name}";
            case 'link': return "/etc/nginx/sites-enabled/{$this->name}";
        }
        return parent::default($property);
    }

}