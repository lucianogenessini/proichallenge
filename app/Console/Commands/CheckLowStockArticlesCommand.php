<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Article;
use App\Models\User;
use App\Notifications\LowStockReport;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class CheckLowStockArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the stock of items is less than 10 and send a notification to all administrators.';

    public const MIN_STOCK = 10;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $articles = Article::where('stock', '<', self::MIN_STOCK)->get();

        if ($articles->isEmpty()) {
            $this->info('There are no articles with stock less than '.self::MIN_STOCK);
            return;
        }

        $file_path = $this->createArticlesFile($articles);

        $admin_users = User::whereHas('roles', function($query){
            $query->where('name', Role::SUPER_ADMIN_ROLE->value);
        })->get();
        
        if ($admin_users->isEmpty()) {
            $this->warn('No administrators found.');
            return;
        }

        Notification::send($admin_users, new LowStockReport($file_path, self::MIN_STOCK));
        
        $this->info('Low stock report sent to administrators.');
    }

    /**
     * Create the file of articles to send
     * 
     * @param Collection $articles
     * 
     * @return string
     */
    protected function createArticlesFile(Collection $articles): string 
    {
        $file_path = 'articles/low_stock_' . now()->format('Y_m_d') . '.csv';
        
        $directory = storage_path('app/articles');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen(storage_path('app/' . $file_path), 'w');

        fputcsv($file, ['Article Name', 'Stock']);

        foreach ($articles as $article) {
            fputcsv($file, [$article->name, $article->stock]);
        }

        fclose($file);

        return $file_path;
    }
}
