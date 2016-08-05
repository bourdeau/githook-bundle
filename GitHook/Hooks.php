<?php
declare(strict_types = 1);

namespace Bourdeau\Bundle\GitHookBundle\GitHook;

use Composer\Script\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Hooks
 *
 * Manage Git hooks creation
 * It will be called each time you run composer install or update
 */
class Hooks
{
    /**
     * Will create a pre-push hook
     *
     * @param Event $event
     *
     * @return boolean
     */
    public static function create(Event $event): bool
    {
        $output = $event->getIO();

        $data = '
            #!/usr/bin/php
            <?php
            require __DIR__ . \'/../../vendor/autoload.php\';
            use Bourdeau\Bundle\GitHookBundle\GitHook\Prepush;
            $console = new Prepush();
            $console->run();
        ';

        $path = __DIR__.'/../../../../.git/hooks/pre-push';
        $prePush = file_put_contents($path, trim($data));

        if (!$prePush) {
            $output->write(sprintf("<error>Couldn't create pre-push hook in %s</error>", $path));

            return false;
        }

        $resultEx = shell_exec(sprintf("chmod +x %s", $path));

        if (!$prePush) {
            $output->write(sprintf("<error>Couldn't chmod +x pre-push hook in %s</error>", $path));

            return false;
        }

        $output->write(sprintf("<info>Pre-push hook was created in %s</info>", $path));

        return true;
    }
}
