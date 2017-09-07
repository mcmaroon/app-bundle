<?php
namespace App\AppBundle\Helper;

/**
 * @link http://robo.li/ Robo.li helper
 */
abstract class RoboLi extends \Robo\Tasks
{

    public function appPhpInfo()
    {
        $this->_exec('php -i');
        $this->_exec('php -v');
    }

    public function appInstall($withComposer = true)
    {
        if ($withComposer) {
            $this->taskComposerUpdate()->run();
        }
        $this->appDbUpdate();
        $this->_exec('git checkout bin/symfony_requirements');
        $this->_exec('git checkout var/SymfonyRequirements.php');
        $this->_exec('git checkout web/config.php');
        $this->_exec('git status');
    }

    public function appUninstall()
    {
        $this->taskExec('php bin/console doctrine:schema:drop --force')->run();
        $this->appClean();
    }

    public function appReinstall()
    {
        $this->taskComposerUpdate()->run();
        $this->appUninstall();
        $this->appInstall(false);
    }

    public function appReload()
    {
        $this->appUninstall();
        $this->appInstall(false);
    }

    public function appDbUpdate()
    {
        $this->appClearCache();
        $this->taskExec('php bin/console doctrine:schema:update --force --dump-sql')->run();
        $this->taskExec('php bin/console doctrine:schema:validate')->run();
    }

    public function appTest()
    {
        $this->taskExec('php bin/console doctrine:schema:validate')->run();
        $this->taskPhpUnit('vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit ./src')->run();
    }

    /**
     * Clear cache
     */
    public function appClearCache()
    {
        $dirs = [
            'var/cache', 'var/logs', 'var/tmp',
            'web/bundles', 'web/assetic', 'web/css', 'web/images', 'web/js', 'web/media/cache',
            'public_html/bundles', 'public_html/assetic', 'public_html/css', 'public_html/images', 'public_html/js', 'public_html/media/cache',
        ];
        $this->cleanDirectories($dirs);
    }

    /**
     * Clear cache Alias
     */
    public function appCC()
    {
        $this->appClearCache();
    }

    /**
     * Clear cache,logs,tmp,assets
     */
    public function appClean()
    {
        $this->appClearCache();
        $dirs = [
            'web/media',
            'public_html/media',
        ];
        $this->cleanDirectories($dirs);
    }

    public function appCron()
    {
        $this->taskExec('php bin/console app:stat')->run();
    }

    /**
     * Composer dump autoload
     */
    public function appComposerDumpAutoLoad()
    {
        $this->taskComposerDumpAutoload()->run();
    }

    public function appComposerRequire()
    {
        $tcr = $this->taskComposerRequire();
        $tcr->dependency('phpunit/phpunit', '4.8.35');
        $tcr->dependency('symfony/phpunit-bridge', '3.3.2');
        $tcr->dependency('consolidation/robo', '1.0.8');
        $tcr->dependency('sebastian/phpcpd', '2.0.4');
        $tcr->dependency('friendsofphp/php-cs-fixer', '2.3.2');
        $tcr->dependency('squizlabs/php_codesniffer', '3.0.0a1');
        $tcr->dependency('phpmd/phpmd', '2.6.0');
        $tcr->dependency('nikic/php-parser', '2.1.1');
        $tcr->dependency('phpmetrics/phpmetrics', '2.0.0');
        $tcr->dependency('codeception/codeception', '2.2.9');

        $tcr->dev();
        $tcr->run();

        $tcr = $this->taskComposerRequire();
        $tcr->dependency('twig/twig', '1.34.3');
        $tcr->dependency('symfony/assetic-bundle', 'v2.8.1');
        $tcr->dependency('stof/doctrine-extensions-bundle', 'v1.2.2');
        $tcr->dependency('knplabs/knp-paginator-bundle', '2.5.1');
        $tcr->dependency('knplabs/knp-menu-bundle', 'v2.1.1');
        $tcr->dependency('guzzlehttp/guzzle', '6.2.2');
        $tcr->dependency('liip/imagine-bundle', '1.4.1');
        $tcr->dependency('gregwar/captcha-bundle', '2.0.3');
        $tcr->dependency('twig/extensions', '1.3');
        $tcr->dependency('cboden/ratchet', '0.3.*');
//        $tcr->dependency('cache/cache-bundle', '0.4.1');
//        $tcr->dependency('cache/adapter-bundle', '0.3.3');
//        $tcr->dependency('cache/redis-adapter', '0.4.1');
        $tcr->run();
    }

    /**
     * Run php tool
     */
    public function appTools($toolName = '')
    {
        $tools = [
            'php-cs-fixer' => [
                'title' => 'PHP Coding Standards Fixer',
                'desc' => 'The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents and many more.',
                'args' => 'fix ./src',
            ],
            'phpcpd' => [
                'title' => 'PHP Copy/Paste Detector (PHPCPD)',
                'desc' => 'is a Copy/Paste Detector (CPD) for PHP code.',
                'args' => './src --exclude Lib --exclude Utility',
            ],
            'phpcs' => [
                'title' => 'PHP_CodeSniffer',
                'desc' => 'PHP_CodeSniffer tokenizes PHP, JavaScript and CSS files and detects violations of a defined set of coding standards. ',
                'args' => './src',
            ],
            'phpcbf' => [
                'title' => 'PHP_CodeSniffer',
                'desc' => 'PHP_CodeSniffer tokenizes PHP, JavaScript and CSS files and detects violations of a defined set of coding standards. ',
                'args' => './src',
            ],
            'phpmd' => [
                'title' => 'PHPMD - PHP Mess Detector',
                'desc' => 'This is the project site of PHPMD. It is a spin-off project of PHP Depend and aims to be a PHP equivalent of the well known Java tool PMD. PHPMD can be seen as an user friendly and easy to configure frontend for the raw metrics measured by PHP Depend.',
                'args' => './src text codesize',
            ],
            'phpmetrics' => [
                'title' => 'PhpMetrics',
                'desc' => 'Gives metrics about PHP project and classes.',
                'args' => './src --report-html=./var/logs/phpmetrics',
            ],
        ];

        if (empty($toolName) || !array_key_exists($toolName, $tools)) {
            $this->say("Available options: ");
            $this->say("--------------------------------------------");
            foreach ($tools as $key => $tool) {
                $this->say("Option: " . $key);
                $this->say("Name: " . $tool['title']);
                $this->say("Description: " . $tool['desc']);
                $this->say("--------------------------------------------");
            }
            exit();
        }
        $this->taskExec('vendor\bin\\' . $toolName . ' ' . $tools[$toolName]['args'])->run();
    }

    protected function cleanDirectories(array $dirs)
    {
        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                try {
                    $this->_cleanDir($dir);
                } catch (\Exception $exc) {
                    $this->say($exc->getMessage());
                }
            }
        }
    }
}
