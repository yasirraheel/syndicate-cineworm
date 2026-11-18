<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time brand update for the live database.
 *
 * The file rebrand (CutCost and Sharesub to Syndicate) does not touch
 * data that was seeded earlier, and ContentSeeder only uses
 * firstOrCreate for site settings, so re-running the normal seeders
 * leaves the old names in place. This seeder walks the display text
 * columns that can carry the brand name and rewrites the old names in
 * place. It never inserts, deletes or truncates anything, it only
 * updates rows whose text actually contains an old brand name, so it
 * is safe to run on the live database and safe to run twice.
 *
 * Run it once on the server after pulling the rebrand:
 *   php artisan db:seed --class=RebrandToSyndicateSeeder
 */
class RebrandToSyndicateSeeder extends Seeder
{
    protected array $replacements = [
        'ShareSub' => 'Syndicate',
        'Sharesub' => 'Syndicate',
        'SHARESUB' => 'SYNDICATE',
        'sharesub' => 'syndicate',
        'CutCost' => 'Syndicate',
        'CUTCOST' => 'SYNDICATE',
        'cutcost' => 'syndicate',
    ];

    /**
     * Table => [row identifier, text columns to sweep].
     * Missing tables or columns are skipped, so the seeder stays safe
     * whatever subset of the schema a given install has.
     */
    protected array $targets = [
        'site_settings' => ['key', ['value']],
        'faqs' => ['id', ['question', 'answer']],
        'brand_faqs' => ['id', ['question', 'answer']],
        'footer_link_groups' => ['id', ['title']],
        'footer_links' => ['id', ['label']],
        'pages' => ['id', ['title', 'excerpt', 'content']],
        'features' => ['id', ['title', 'text']],
        'how_it_works_steps' => ['id', ['title', 'text']],
        'testimonials' => ['id', ['quote']],
        'press_items' => ['id', ['outlet_name', 'logo_text']],
        'reassurance_items' => ['id', ['title', 'text']],
        'templates' => ['id', ['name']],
    ];

    public function run(): void
    {
        $total = 0;

        foreach ($this->targets as $table => [$identifier, $columns]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $identifier)) {
                continue;
            }

            $columns = array_values(array_filter(
                $columns,
                static fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($columns === []) {
                continue;
            }

            foreach (DB::table($table)->select(array_merge([$identifier], $columns))->cursor() as $row) {
                $updates = [];

                foreach ($columns as $column) {
                    $old = $row->{$column};

                    if (! is_string($old) || $old === '') {
                        continue;
                    }

                    $new = str_replace(
                        array_keys($this->replacements),
                        array_values($this->replacements),
                        $old,
                    );

                    if ($new !== $old) {
                        $updates[$column] = $new;
                    }
                }

                if ($updates !== []) {
                    DB::table($table)->where($identifier, $row->{$identifier})->update($updates);
                    $total++;
                    $this->command?->line($table.' ['.$row->{$identifier}.'] updated: '.implode(', ', array_keys($updates)));
                }
            }
        }

        $this->command?->info('RebrandToSyndicateSeeder: '.$total.' rows updated.');
    }
}
