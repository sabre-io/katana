<?php

namespace Sabre\Katana\Bin;

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Server\Server;
use Hoa\Console;
use Hoa\Console\Cursor;
use Hoa\Console\Window;
use Hoa\Console\Chrome\Text;

/**
 * Install page.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Install extends AbstractCommand
{
    protected $options = [
        ['no-verbose', Console\GetOption::NO_ARGUMENT, 'V'],
        ['help',       Console\GetOption::NO_ARGUMENT, 'h'],
        ['help',       Console\GetOption::NO_ARGUMENT, '?']
    ];

    /**
     * Main method.
     *
     * @return int
     */
    public function main()
    {
        $verbose = Console::isDirect(STDOUT) || !OS_WIN;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);
                    break;

                case 'V':
                    $verbose = !$v;
                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();
                    break;

            }
        }

        if (true === Installer::isInstalled()) {

            echo 'The application is already installed.', "\n";
            return 1;

        }

        $oldTitle = Window::getTitle();
        Window::setTitle('Installation of sabre/katana');

        $form = [
            'baseUrl'  => '/',
            'login'    => null,
            'email'    => null,
            'password' => null,
            'database' => [
                'driver'   => 'sqlite',
                'host'     => '',
                'port'     => '',
                'name'     => '',
                'username' => '',
                'password' => ''
            ]
        ];

        $readline = new Console\Readline();

        if (true === $verbose) {

            $windowWidth   = Window::getSize()['x'];
            $labelMaxWidth = 35;
            $inputMaxWidth = $windowWidth - $labelMaxWidth;
            $numberOfSteps = 5;

            $input = function($default = '') use($inputMaxWidth) {
                return Text::colorize(
                    $default .
                    str_repeat(
                        ' ',
                        $inputMaxWidth - mb_strlen($default)
                    ),
                    'foreground(black) background(#cccccc)'
                );
            };

            $resetInput = function($default = '') use($input, $labelMaxWidth) {
                Cursor::move('→', $labelMaxWidth);
                echo $input($default);
                Cursor::move('LEFT');
                Cursor::move('→', $labelMaxWidth);
                Cursor::colorize('foreground(black) background(#cccccc)');
            };

            echo
                Text::colorize(
                    'Installation of sabre/' . "\n" . Welcome::LOGO,
                    'foreground(yellow)'
                ), "\n\n",
                'Choose the base URL:               ', $input('/'), "\n",
                'Choose the administrator login:    ', $input(), "\n",
                'Choose the administrator password: ', $input(), "\n",
                'Choose the administrator email:    ', $input(), "\n",
                'Choose the database driver:        ', '🔘 SQLite ⚪️ MySQL', "\n";

            Window::scroll('↑', 10);
            Cursor::move('↑', 10);

            Cursor::move('↑', $numberOfSteps);
            Cursor::move('→', $labelMaxWidth);
            Cursor::colorize('foreground(black) background(#cccccc)');

            // Disable arrow up and down.
            $_no_echo = function($readline) {
                return $readline::STATE_NO_ECHO;
            };
            $readline->addMapping("\e[A", $_no_echo);
            $readline->addMapping("\e[B", $_no_echo);

            $step = function($index, $label, Callable $validator, $errorMessage, $default = '')
                    use($numberOfSteps, &$readline, $resetInput, $labelMaxWidth) {

                do {

                    $out = $readline->readLine();

                    if (empty($out)) {
                        $out = $default;
                    }

                    $valid = $validator($out);

                    if (true !== $valid) {

                        Cursor::move('↑');
                        $resetInput($default);
                        Cursor::save();
                        Cursor::move('LEFT');
                        Cursor::move('↓', $numberOfSteps - $index + 1);

                        list($_title, $_message) = explode("\n", $errorMessage);

                        Cursor::colorize('foreground(white) background(red)');
                        echo $_title, "\n";

                        Cursor::colorize('foreground(red) background(normal)');
                        echo $_message;

                        Cursor::restore();

                    } else {

                        Cursor::save();
                        Cursor::move('LEFT');
                        Cursor::move('↓', $numberOfSteps - $index - 1);
                        Cursor::colorize('normal');
                        Cursor::clear('↓');
                        Cursor::restore();

                    }

                } while (true !== $valid);

                if ($numberOfSteps !== $index + 1) {
                    Cursor::move('→', $labelMaxWidth);
                }

                return $out;

            };

            $progress = function($percent, $message) use($windowWidth) {

                static $margin = 4;
                $barWidth      = $windowWidth - $margin * 2;

                Cursor::move('LEFT');
                Cursor::move('↑', 1);
                Cursor::clear('↓');

                if ($percent <= 0) {
                    $color = '#c74844';
                } elseif ($percent <= 25) {
                    $color = '#cb9a3d';
                } elseif ($percent <= 50) {
                    $color = '#dcb11e';
                } elseif ($percent <= 75) {
                    $color = '#aed633';
                } else {
                    $color = '#54b455';
                }

                echo str_repeat(' ', $margin);

                Cursor::colorize('foreground(' . $color . ') background(' . $color . ')');
                echo str_repeat('|', ($percent * $barWidth) / 100);

                Cursor::move('LEFT ↓');

                Cursor::colorize('background(normal)');
                echo str_repeat(' ', $margin) . $message;

                Cursor::colorize('normal');
                sleep(1);

                return;

            };

        } else {

            $step = function($index, $label, Callable $validator, $errorMessage, $default = '')
                    use(&$readline) {

                do {

                    echo $label;

                    if (!empty($default)) {
                        echo ' [default: ', $default, ']';
                    }

                    $out = $readline->readLine(': ');

                    if (empty($out)) {
                        $out = $default;
                    }

                    $valid = $validator($out);

                    if (true !== $valid) {
                        echo $errorMessage, "\n";
                    }

                } while (true !== $valid);

                return $out;

            };

            $progress = function($percent, $message) {

                echo $message, "\n";

            };

        }

        $form['baseUrl'] = $step(
            0,
            'Choose the base URL',
            function($baseUrl) {
                return Installer::checkBaseUrl($baseUrl);

            },
            'Base URL must start and end by a slash' . "\n" .
            'Check the Section “The base URL” on http://sabre.io/dav/gettingstarted/.',
            '/'
        );

        $form['login'] = $step(
            1,
            'Choose the administrator login',
            function($administratorLogin) {
                return Installer::checkLogin($administratorLogin);
            },
            'Login must not be empty' . "\n" .
            'How then would call you?'
        );

        $oldReadline = $readline;
        $readline    = new Console\Readline\Password();
        $form['password'] = $step(
            2,
            'Choose the administrator password',
            function($administratorPassword) {
                return Installer::checkPassword(
                    $administratorPassword .
                    $administratorPassword
                );
            },
            'Password must not be empty' . "\n" .
            'An empty password is not a password anymore!'
        );
        $readline = $oldReadline;

        $form['email'] = $step(
            3,
            'Choose the administrator email',
            function($administratorEmail) {
                return Installer::checkEmail(
                    $administratorEmail .
                    $administratorEmail
                );
            },
            'Email is invalid' . "\n" .
            'The given email seems invalid.'
        );

        $databaseDriver = &$form['database']['driver'];

        if (true === $verbose) {

            Cursor::colorize('normal');

            $radioReadline  = new Console\Readline\Password();
            $radioReadline->addMapping(
                '\e[D',
                function() use($labelMaxWidth, &$databaseDriver) {

                    $databaseDriver = 'sqlite';

                    Cursor::save();
                    Cursor::move('LEFT');
                    Cursor::move('→', $labelMaxWidth);
                    Cursor::clear('→');
                    echo '🔘 SQLite ⚪️ MySQL';
                    Cursor::restore();

                    return;

                }
            );
            $radioReadline->addMapping(
                '\e[C',
                function() use($labelMaxWidth, &$databaseDriver) {

                    $databaseDriver = 'mysql';

                    Cursor::save();
                    Cursor::move('LEFT');
                    Cursor::move('→', $labelMaxWidth);
                    Cursor::clear('→');
                    echo '⚪️ SQLite 🔘 MySQL';
                    Cursor::restore();

                    return;

                }
            );

            Cursor::hide();
            $radioReadline->readLine();
            Cursor::show();
            unset($databaseDriver);

        } else {
            $step(
                4,
                'Choose the database driver (sqlite or mysql)',
                function($databaseDriver) {
                    return in_array($databaseDriver, ['sqlite', 'mysql']);
                },
                'Database driver is invalid' . "\n" .
                'Database driver must be `sqlite` or `mysql`',
                'sqlite'
            );
        }

        $readline->readLine(
            "\n" . 'Ready to install? (Enter to continue, Ctrl-C to abort)'
        );

        echo "\n\n";

        try {

            $progress(5, 'Create configuration file…');

            $configuration = Installer::createConfigurationFile(
                Server::CONFIGURATION_FILE,
                [
                    'baseUrl'  => $form['baseUrl'],
                    'database' => $form['database']
                ]
            );

            $progress(25, 'Configuration file created 👍!');
            $progress(30, 'Create the database…');

            $database = Installer::createDatabase($configuration);

            $progress(50, 'Database created 👍!');
            $progress(55, 'Create administrator profile…');

            Installer::createAdministratorProfile(
                $configuration,
                $database,
                $form['login'],
                $form['email'],
                $form['password']
            );

            $progress(75, 'Administrator profile created 👍!');
            $progress(100, 'sabre/katana is ready!');

        } catch (\Exception $e) {

            $progress(-1, 'An error occured: ' . $e->getMessage());
            return 2;

        }

        Window::setTitle($oldTitle);

        return;
    }

    /**
     * Print the usage.
     *
     * @return void
     */
    public function usage()
    {
        echo
            'Usage  : install <options>', "\n",
            'Options:', "\n",
            $this->makeUsageOptionsList([
                'V'    => 'Be as less verbose as possible.',
                'help' => 'This help.'
            ]);
    }
}
