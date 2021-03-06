<?php

namespace Terranet\Administrator\Console;

use Artisan;
use Illuminate\Console\Command;
use Pingpong\Menus\MenusServiceProvider;
use Terranet\Administrator\ServiceProvider;
use Creativeorange\Gravatar\GravatarServiceProvider;
use Terranet\Administrator\Traits\SessionGuardHelper;
use Czim\Paperclip\Providers\PaperclipServiceProvider;
use DaveJamesMiller\Breadcrumbs\ServiceProvider as BreadcrumbsServiceProvider;

class PublishCommand extends Command
{
    use SessionGuardHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'administrator:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish assets.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->output->title('Please select things to be published:');

        $published = [];
        if ($this->confirm('Routes?', true)) {
            Artisan::call('vendor:publish', [
                '--provider' => ServiceProvider::class,
                '--tag' => 'routes',
            ]);
            $published[] = 'app/Http/Terranet/Administrator/routes.php';
        }

        if ($this->confirm('Configuration?', true)) {
            Artisan::call('vendor:publish', [
                '--provider' => ServiceProvider::class,
                '--tag' => 'config',
            ]);

            $published[] = 'config/administrator.php';
        }

        if ($this->confirm('Views?', false)) {
            Artisan::call('vendor:publish', [
                '--provider' => ServiceProvider::class,
                '--tag' => 'views',
            ]);

            $published[] = 'resources/views/vendor/administrator/*.php';
        }

        if ($this->confirm('Translations?', true)) {
            Artisan::call('vendor:publish', [
                '--provider' => ServiceProvider::class,
                '--tag' => 'translations',
            ]);

            $published[] = 'resources/lang/vendor/administrator';
        }

        Artisan::call('vendor:publish', [
            '--provider' => ServiceProvider::class,
            '--tag' => ['assets', 'boilerplate', 'navigation'],
        ]);

        $published[] = 'resources/assets/administrator';
        $published[] = 'app\\Http\Terranet\\Administrator\\Modules';
        $published[] = 'app\\Http\Terranet\\Administrator\\Dashboard';
        $published[] = 'app\\Http\Terranet\\Administrator\\Navigation.php';

        foreach ($published as $file) {
            $type = preg_match('~\.[a-z0-9]{2,}$~si', $file) ? 'File' : 'Directory';

            $this->line(sprintf(
                '<info>%s</info> <comment>[%s]</comment> <info>has been created.</info>',
                $type, $file
            ));
        }

        $this->publishDependencies();
    }

    protected function publishDependencies()
    {
        $this->dependencies()->each(function ($provider) {
            Artisan::call('vendor:publish', [
                '--provider' => $provider
            ]);
            $this->line(sprintf(
                '<info>%s</info> <comment>[%s]</comment> <info>has been published.</info>',
                'Package', $provider
            ));
        });
    }

    protected function dependencies()
    {
        return collect([
            PaperclipServiceProvider::class,
            BreadcrumbsServiceProvider::class,
            MenusServiceProvider::class,
            GravatarServiceProvider::class,
        ]);
    }
}
