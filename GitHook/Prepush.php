<?php
declare(strict_types = 1);

namespace Bourdeau\Bundle\GitHookBundle\GitHook;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Application;

/**
 * Update
 *
 * Will check the code before submited to origin via Git Hook on server
 */
class Prepush extends Application
{
    private $localRef;
    private $localSha;
    private $remoteRef;
    private $remoteSha;
    private $author;
    private $email;
    private $logName;

    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct('Nazi Tool :)', 'v.0.0.1');

        $line = trim(fgets(STDIN));
        $args = explode(" ", $line);

        // Pushing nothing
        if (count($args) != 4) {
            echo "Error in stdin number of args. See ".__DIR__." (line ".__LINE__.")";
            exit(1);
        }

        $this->localRef = $args[0];
        $this->localSha = $args[1];
        $this->remoteRef = $args[2];
        $this->remoteSha = $args[3];
        $this->logName = $_SERVER['LOGNAME'];

        $this->getAuthor($this->localSha);
    }

    /**
     * Run
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $output = $this->createStyles($output);

        // Welcome
        $this->welcome($output);

        // 1 Changed files
        $this->changedFiles($output);

        // 2 Check shit
        $this->checkStyle('var_dump', $output);
        $this->checkStyle('die', $output);

        //3 Run test
        $this->runTest($output);

        // Everything okay, push accepted
        $output->writeln("<blue>FINAL:</blue>");
        $output->writeln("<info>→ SUCCESS: your push has been accepted! ☺</info>");
        exit(0);
    }

    /**
     * Just a fancy welcome message
     *
     * @param OutputInterface $output
     */
    private function welcome($output)
    {
        $output->writeln("<info>☺ ☺ ☺ ☺ ☺ ☺ ☺ ☺  WELCOME TO GITHOOK v0.0.1 ☺ ☺ ☺ ☺ ☺ ☺ ☺ ☺</info>");

        $output->writeln("");
        $output->writeln(sprintf("<nice>HELLO %s!</nice>", $this->author));
        $output->writeln("");
        $output->writeln(sprintf("<comment>I'm your prepush hook and I'm going to check a few things before I allow your push to origin.</comment>"));
        $output->writeln("");
    }

    /**
     * Display changed files
     *
     * @param OutputInterface $output
     */
    private function changedFiles($output)
    {
        $output->writeln(sprintf("<blue>1)- Files you changed</blue>"));
        $output->writeln("");
        $output->writeln(sprintf($this->checkGitStats()));
    }

    /**
     * Make grep in code to find unwated code
     *
     * @param string          $string
     * @param OutputInterface $output
     */
    private function checkStyle($string, $output)
    {
        $output->writeln(sprintf("<blue>2)- Filthy '%s' you might have left</blue>", $string));
        $output->writeln("");

        $flagFound = shell_exec(sprintf("git grep --color %s HEAD -- `git ls-files | grep -v src/AppBundle/Git/`", $string));

        if ($flagFound) {
            $output->writeln($flagFound);
            $output->writeln("<error>☠ ☠ ☠ You left some crap. Clean your mess. Thanks :) ☠ ☠ ☠ </error>");
            exit(1);
        }

        $output->writeln("<info>→ Ok</info>");
        $output->writeln("");
    }

    private function runTest($output)
    {
        $output->writeln("<blue>3)- Running test</blue>");
        $output->writeln("");

        // PHPCS
        $output->writeln("<comment>PHPCS:</comment>");
        $output->writeln("");

        $results = shell_exec("composer run-script phpcs");

        if ($results) {
            $output->writeln($results);
            $output->writeln("<error>☠ ☠ ☠ PHPCS FAILED ☠ ☠ ☠ </error>");
            exit(1);
        }
        $output->writeln("<info>→ Ok</info>");
        $output->writeln("");

        // Behat
        $output->writeln("<comment>BEHAT:</comment>");
        $output->writeln("");

        $results = shell_exec("composer run-script behat");

        $output->writeln("<info>→ Ok</info>");
        $output->writeln("");
    }

    /**
     * Return git diff command
     *
     * @return strinf
     */
    private function checkGitStats(): string
    {
        return shell_exec("git diff --stat --cached origin/master");
    }

    /**
     * Available styles:
     *     - error
     *
     * @param OutputInterface $output
     *
     * @return OutputInterface
     */
    private function createStyles($output): OutputInterface
    {
        $style = new OutputFormatterStyle('white', 'red', array('bold', 'blink'));
        $output->getFormatter()->setStyle('error', $style);

        $style = new OutputFormatterStyle('white', 'green', array('bold', 'blink'));
        $output->getFormatter()->setStyle('valid', $style);

        $style = new OutputFormatterStyle('white', 'blue', array('bold', 'blink'));
        $output->getFormatter()->setStyle('nice', $style);

        $style = new OutputFormatterStyle('blue');
        $output->getFormatter()->setStyle('blue', $style);

        $style = new OutputFormatterStyle('cyan');
        $output->getFormatter()->setStyle('cyan', $style);

        $style = new OutputFormatterStyle('magenta');
        $output->getFormatter()->setStyle('magenta', $style);

        return $output;
    }

    /**
     * Get the author & email
     *
     * @param string $commitId
     *
     * @throws \Exception
     */
    private function getAuthor($commitId)
    {
        $data = shell_exec(sprintf('git cat-file -p %s', $commitId));

        if (!preg_match("/author (.*) <(.*)>/", $data, $matches)) {
            throw new \Exception("Couldn't find the author in commit", 1);
        }

        $this->author = $matches[1];
        $this->email = $matches[2];
    }
}
