<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use DiscordWebhooks\Client as WebhookClient;
use DiscordWebhooks\Embed as DiscordEmbed;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Output\OutputInterface;

class WebhookSendCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'webhook:send
        {webhook? : Webhook URL (optional if $WEBHOOK_URL set)}
        {--text= : Body text of message to send (optional)}
        {--tts : Send message with text-to-speech enabled (optional)}
        {--username= : Send message with text-to-speech enabled (optional)}
        {--avatar= : Set avatar to image via URL (optional)}
        {--title= : Set embed title (optional)}
        {--title-url= : Set embed title URL (optional)}
        {--description= : Set embed description (optional)}
        {--color= : Set embed color (hex/decimal color) (optional)}
        {--thumbnail= : Set thumbnail to image located via URL (optional)}
        {--author= : Set embed author name (optional)}
        {--author-icon= : Set embed author icon (optional)}
        {--author-url= : Set embed author URL (optional)}
        {--image= : Set the embed image URL (optional)}
        {--footer= : Set footer text (optional)}
        {--footer-icon= : Set footer icon (optional)}
        {--field=* : Set field (optional)}
        {--timestamp : Set current time in footer (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Send a Discord webhook (message or embed)';

     /**
     * Set webhook URL
     *
     * @var string
     */
    protected $url;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->url = env('WEBHOOK_URL') ?? $this->argument('webhook');
        if ($this->url == null) {
            $this->error("Error: You must either have the environment variable \$WEBHOOK_URL set or must pass it as parameter.");
            exit(1);
        }
        $webhook = new WebhookClient($this->url);
        return $this->pipeline($webhook);
    }

    /**
     * Print field option usage
     *
     * @return mixed
     */
    private function printFieldUsage(): void
    {
        $this->comment("Usage: ⬇");
        $this->table(['Parameter', 'Type', 'Example', 'Limit', 'Required'], [
            ['1: Field Name', 'string', 'Author', '256 characters', '✔'],
            ['2: Field Value', 'string', 'Sheldon Rupp', '1024 characters', '✔'],
            ['3: Inline', 'boolean', 'true', 'N/A', '✖'],
        ], 'box');
        $this->alert("Example: --field \"author, sheldon\" | --field \"author, sheldon, true\"");
    }
    /**
     * Execute pipeline
     *
     * @return mixed
     */
    private function pipeline(WebhookClient $webhook)
    {
        $embed = new DiscordEmbed();
        if ($this->is_embed()) {
            if (strlen($this->option('title')) > 256) {
                $this->error('Error: The title must be 256 characters maximum.');
                exit(1);
            } else {
                $embed->title($this->option('title'));
            }
            if (strlen($this->option('description')) > 2048) {
                $this->error('Error: The description must be 2048 characters maximum.');
                exit(1);
            } else {
                $embed->description($this->option('description'));
            }
            if (count($this->option('field')) > 25) {
                $this->error('Error: You may have 25 fields maximum.');
                exit(1);
            }
            for ($i = 0; $i != count($this->option('field')); $i++) {
                $ex = explode(', ', $this->option('field')[$i]);
                if (count($ex) != 2 && count($ex) != 3) {
                    $this->error("Error: You must pass at least 2 arguments to field #". ($i + 1));
                    $this->printFieldUsage();
                    exit(1);
                }
                if (!empty($ex[2])) {
                    if ($ex[2] != "true" && $ex[2] != "false") {
                        $this->error("Error: You must pass true or false as the 3rd argument to field #". ($i + 1));
                        $this->printFieldUsage();
                        exit(1);
                    }
                    $ex[2] = ($ex[2] == "true") ? true : false;
                    if (strlen($ex[0]) > 256) {
                        $this->error("Error: Too many characters in parameter 1 of field #". ($i + 1));
                        $this->printFieldUsage();
                        exit(1);
                    }
                    if (strlen($ex[1]) > 1024) {
                        $this->error("Error: Too many characters in parameter 2 of field #". ($i + 1));
                        $this->printFieldUsage();
                        exit(1);
                    }
                    $embed->field($ex[0], $ex[1], $ex[2]);
                }
            }
            if ($this->option('color')) $embed->color($this->isValidColor());
            if ($this->option('thumbnail') && !$this->isValidImage($this->option('thumbnail'))) {
                $this->error("Error: The URL of your thumbnail is invalid. Make sure it is a direct URL");
                exit(1);
            } else {
                $embed->thumbnail($this->option('thumbnail'));
            }
            if (strlen($this->option('author')) > 256) {
                $this->error('Error: The author text must be 256 characters maximum.');
                exit(1);
            } else {
                $embed->author($this->option('author'), $this->option('author-url') ?? '', $this->option('author-icon') ?? '');
            }
            if ($this->option('image') && !$this->isValidImage($this->option('image'))) {
                $this->error("Error: The URL of your image is invalid. Make sure it is a direct URL");
                exit(1);
            } else {
                $embed->image($this->option('image'));
            }
            if (strlen($this->option('footer')) > 2048) {
                $this->error("Error: The footer must be 2048 characters maximum");
                exit(1);
            } else {
                $embed->footer($this->option('footer'), $this->option('footer-icon') ?? '');
            }
            if ($this->option('timestamp')) $embed->timestamp(date("c"));


            $webhook->embed($embed);
        }
        if (strlen($this->option('text')) > 2000) {
            $this->error("Error: The text must be 2000 characters maximum");
            exit(1);
        } else {
            $webhook->message($this->option('text') ?? '');
        }
        if (strlen($this->option('username')) > 80) {
            $this->error("Error: The username must be 80 characters maximum");
            exit(1);
        } else {
            $webhook->username($this->option('username') ?? '');
        }
        if ($this->option('avatar') && !$this->isValidImage($this->option('avatar'))) {
            $this->error("Error: The URL of your avatar is invalid. Make sure it is a direct URL");
            exit(1);
        } else {
            $webhook->avatar($this->option('avatar'));
        }
        if ($this->option('tts')) $webhook->tts(true);

        $this->isValidWebhook();

        try {
            $webhook->send();
            $this->info("Successfully sent webhook!");
        } catch (\Exception $e) {
            $this->error("Error: An exception was thrown");
            $this->error($e, OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    /**
     * Check if image is valid
     *
     * @return bool
     */
    private function isValidImage(string $image_url): bool
    {
        if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
            $response = Http::get($image_url);
            if (!$response->successful()) {
                return false;
            }
            if (strpos($response->header('Content-Type'), 'image') === false) {
                return false;
            }
            return true;
        }
    }

    /**
     * Check if color is valid
     *
     * @return bool
     */
    private function isValidColor(): int
    {
        if (!$this->option('color'))
            return (null);
        try {
            $color = $this->option('color');
            (new DiscordEmbed)->color($color);
            return (is_int($color) ? $color : hexdec($color));
        } catch (\Exception $e) {
            $this->error('Error: Color invalid. Make sure it is in either hexadecimal or decimal format.');
            $this->alert('Example: --color 15DBA3 | --color 0x15DBA3 | --color 1432483');
            exit(1);
        }
    }

    /**
     * Check if webhook is valid
     *
     * @return bool
     */
    private function isValidWebhook(): bool
    {
        $host = parse_url($this->url, PHP_URL_HOST);
        if ($host != "discordapp.com" && $host != "ptb.discordapp.com") {
            $this->error("Error: The webhook URL you passed is invalid. Domain is \"${host}\"");
            return (1);
        }
        $this->getValidWebhook();
        return (0);
    }

    /**
     * Check if webhook is valid via GET
     *
     * @return bool
     */
    private function getValidWebhook(): bool
    {
        $response = Http::get($this->url);
        if ($response->offsetExists('message') && $response->offsetExists('code')) {
            $this->error("Error: Invalid webhook URL.");
            exit(1);
        }
        if (!$response->successful()) {
            $this->error("Error: An error has occured.");
            exit(1);
        }
        if (
            !$response->offsetExists('type') ||
            !$response->offsetExists('id') ||
            !$response->offsetExists('name') ||
            !$response->offsetExists('channel_id') ||
            !$response->offsetExists('guild_id') ||
            !$response->offsetExists('token')
        ) {
            $this->error("Error: Invalid webhook URL.");
            exit(1);
        } else {
            $this->comment("Webhook URL seems to be valid...", OutputInterface::VERBOSITY_VERBOSE);
        }
        return (0);
    }

    /**
     * Define wether we are dealing with a embed or not.
     *
     * @return bool
     */
    private function is_embed(): bool
    {
        return (
            $this->option('title') ||
            $this->option('description') ||
            $this->option('color') ||
            $this->option('thumbnail') ||
            $this->option('author') ||
            $this->option('author-icon') ||
            $this->option('author-url') ||
            $this->option('image') ||
            $this->option('footer') ||
            $this->option('footer-icon') ||
            $this->option('timestamp')
        );
    }
}
