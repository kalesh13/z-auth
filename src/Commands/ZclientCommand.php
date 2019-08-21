<?php

namespace Zauth\Commands;

use Illuminate\Console\Command;
use Zauth\Http\Controller\ClientController;
use Zauth\Http\Exceptions\ClientCreateException;

class ZclientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zclient:make {name : Name of the client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the root clients required for the application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->error('Enter a valid client name.');
            return;
        }
        try {
            $client = (new ClientController())->create($name);

            if ($client->save()) {
                $this->info("Client $name created successfully. Save the credentials safely");
                $this->info('Client Name: ' . $name);
                $this->info('Client Id: ' . $client->getClientId());
                $this->info('Client Secret: ' . $client->getClientSecret());
                return;
            }
            // Error saving the client. Show an error message;
            $this->error('Error saving the client');
        } catch (ClientCreateException $e) {
            // If client creation was a failure, show the
            // error message on the console.
            $this->error($e->getMessage());
        }
    }
}
