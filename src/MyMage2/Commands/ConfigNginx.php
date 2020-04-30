<?php

namespace Osmianski\MyMage2\Commands;

use Osmianski\MyMage2\Domain;
use Osmianski\MyMage2\Mage;
use OsmScripts\Core\Command;
use OsmScripts\Core\Files;
use OsmScripts\Core\Script;
use OsmScripts\Core\Shell;
use Symfony\Component\Console\Input\InputOption;

/** @noinspection PhpUnused */

/**
 * `config:nginx` shell command class.
 *
 * Dependencies:
 *
 * @property Mage $mage @required
 * @property Shell $shell @required Helper for running commands in local shell
 * @property Files $files @required Helper for generating files.
 *
 * Calculated properties:
 *
 * @property bool $force
 * @property string $path
 * @property Domain[] $domains
 * @property string $snippet_filename
 * @property string $fastcgi_pass
 *
 */
class ConfigNginx extends Command
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'mage': return $script->singleton(Mage::class);
            case 'shell': return $script->singleton(Shell::class);
            case 'files': return $script->singleton(Files::class);

            case 'force': return $this->input->getOption('force');
            case 'path': return $script->cwd;
            case 'domains': return $this->getDomains();
            case 'snippet_filename': return "/etc/nginx/snippets/" .
                "magento{$this->mage->version}-nginx.conf";
            case 'fastcgi_pass': return $this->input->getOption('fastcgi_pass')
                ?: $this->getFastcgiPass();

        }

        return parent::default($property);
    }

    protected function getDomains() {
        $db = new \PDO("mysql:host={$this->mage->db_host};dbname={$this->mage->db_name}",
            $this->mage->db_user, $this->mage->db_password);

        $sql = "SELECT `value` FROM `{$this->mage->db_prefix}core_config_data` " .
            "WHERE `path` = 'web/unsecure/base_url'";

        $result = [];

        foreach ($db->query($sql) as $row) {
            if (preg_match('|^https?://(?<domain>[^/]+)/|ui', $row['value'], $match)) {
                $result[$match['domain']] = new Domain(['name' => $match['domain']]);
            }
        }

        return $result;
    }

    protected function getFastcgiPass() {
        $version = implode('.', array_slice(
            explode('.', phpversion()), 0, 2));

        $filename = is_file("/usr/sbin/php-fpm{$version}")
            ? "/var/run/php/php{$version}-fpm.sock"
            : "/var/run/php/php-fpm.sock";

        return "unix:{$filename}";
    }
    #endregion

    protected function configure() {
        $this
            ->setDescription("Adds current Magento 2 project to Nginx configuration")
            ->addOption('force', 'f', InputOption::VALUE_NONE,
                "Overwrite configuration files. By default, once the files are generated, they stay the same")
            ->addOption('fastcgi_pass', null, InputOption::VALUE_OPTIONAL,
                "Address of a PHP FastCGI server. If omitted, 'unix:/var/run/php/phpX.Y-fpm.sock', where X.Y - version of PHP CLI used to execute this command.");
    }

    protected function handle() {
        $this->mage->verify();
        $this->mage->verifyInstalled();

        if ($this->force || !is_file($this->snippet_filename)) {
            $this->files->save($this->snippet_filename, $this->renderSnippet());
            chmod($this->snippet_filename, 0644);
        }

        foreach ($this->domains as $domain) {
            if ($this->force || !is_file($domain->filename)) {
                $this->files->save($domain->filename,
                    $this->files->render('nginx_config', [
                        'path' => $this->path,
                        'domain' => $domain->name,
                        'snippet_filename' => $this->snippet_filename,
                    ]));
                chmod($domain->filename, 0644);
            }

            if ($this->force || !is_link($domain->link)) {
                if (is_link($domain->link)) {
                    unlink($domain->link);
                }
                symlink(realpath($domain->filename), $domain->link);
            }
        }

        $this->shell->run('service nginx restart');
    }

    protected function renderSnippet() {
        $lines = file('nginx.conf.sample');
        $result = [];

        foreach ($lines as $i => $line) {
            if (!preg_match('/(?<indent>\\s+)fastcgi_pass\\s+fastcgi_backend;/u', $line, $match)) {
                $result[] = $line;
                continue;
            }

            $result[] = "{$match['indent']}fastcgi_pass {$this->fastcgi_pass};\n";

            if (!isset($lines[$i+1])) {
                continue;
            }
            if (mb_strpos($lines[$i + 1], 'fastcgi_buffers') !== false) {
                continue;
            }

            $result[] = "{$match['indent']}fastcgi_buffers 1024 4k;\n";
        }

        return implode($result);
    }

}