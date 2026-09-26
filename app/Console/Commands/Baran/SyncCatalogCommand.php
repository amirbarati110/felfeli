<?php

namespace App\Console\Commands\Baran;

use App\Services\Integration\CatalogSynchronizer;
use App\Services\Integration\IntegrationManager;
use Illuminate\Console\Command;

class SyncCatalogCommand extends Command
{
    protected $signature = 'baran:sync-catalog
        {--driver= : baran_menu یا fixture}
        {--keep-missing : کالاهای غایب در منبع را غیرفعال نکن}';

    protected $description = 'همگام‌سازی کاتالوگ محصولات از منوی باران (قیمت/موجودی/نام؛ بدون عکس)';

    public function handle(IntegrationManager $manager): int
    {
        $source = $manager->catalogSource($this->option('driver') ?: null);

        $this->info("منبع: {$source->name()}");

        $summary = (new CatalogSynchronizer($source))->sync(! $this->option('keep-missing'));

        $this->table(
            ['ساخته', 'به‌روز', 'رد', 'خطا', 'غیرفعال‌شده', 'کل'],
            [[
                $summary['created'], $summary['updated'], $summary['skipped'],
                $summary['failed'], $summary['deactivated_missing'], $summary['total'],
            ]],
        );

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
