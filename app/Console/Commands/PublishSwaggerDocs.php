<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishSwaggerDocs extends Command
{
    protected $signature = 'swagger:publish';
    protected $description = 'Publish Swagger documentation to public directory';

    public function handle()
    {
        $source = storage_path('api-docs/api-docs.json');
        $destination = public_path('api-docs.json');

        if (!File::exists($source)) {
            $this->error('Swagger documentation has not been generated yet.');
            $this->info('Running l5-swagger:generate...');
            $this->call('l5-swagger:generate');
        }

        File::copy($source, $destination);
        File::chmod($destination, 0644);

        $this->info('Swagger documentation has been published to public directory.');
    }
}