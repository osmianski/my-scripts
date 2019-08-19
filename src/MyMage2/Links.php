<?php

namespace Osmianski\MyMage2;

class Links
{
    /**
     * @return string[]
     */
    public function get() {
        return [
            'app/code/Manadev' => 'manadev-products/code/Manadev',
            'app/code/ManadevTest' => 'manadev-products/code/ManadevTest',
            'app/code/ManadevGarage' => 'manadev-products/code/ManadevGarage',
            'app/design/frontend/Manadev' => 'manadev-products/design/frontend/Manadev',
            'app/design/frontend/ManadevTest' => 'manadev-products/design/frontend/ManadevTest',
            'bin/mana' => 'manadev-products/bin/mana',
        ];
    }
}