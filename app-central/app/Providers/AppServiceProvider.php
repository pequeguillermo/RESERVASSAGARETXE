<?php

namespace App\Providers {

    use Illuminate\Support\Facades\Vite;
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            //
        }

        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Vite::prefetch(concurrency: 3);

            if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }

            \Illuminate\Support\Facades\Mail::extend('resend_http', function (array $config = []) {
                return new \App\Mail\Transport\ResendApiTransport($config['key'] ?? env('MAIL_PASSWORD', ''));
            });
        }
    }
}

namespace App\Mail\Transport {
    use Symfony\Component\Mailer\SentMessage;
    use Symfony\Component\Mailer\Transport\AbstractTransport;
    use Illuminate\Support\Facades\Http;
    use Symfony\Component\Mime\MessageConverter;

    class ResendApiTransport extends AbstractTransport
    {
        public function __construct(protected string $key)
        {
            parent::__construct();
        }

        protected function doSend(SentMessage $message): void
        {
            $email = MessageConverter::toEmail($message->getOriginalMessage());
            
            $payload = [
                'from' => $email->getFrom()[0]->getAddress(),
                'to' => collect($email->getTo())->map(fn($addr) => $addr->getAddress())->toArray(),
                'subject' => $email->getSubject(),
                'html' => $email->getHtmlBody() ?? $email->getTextBody(),
            ];

            if ($email->getReplyTo()) {
                $payload['reply_to'] = $email->getReplyTo()[0]->getAddress();
            }

            $response = Http::withToken($this->key)->post('https://api.resend.com/emails', $payload);

            if ($response->failed()) {
                throw new \Exception('Resend API Error: ' . $response->body());
            }
        }
        
        public function __toString(): string
        {
            return 'resend-api';
        }
    }
}
