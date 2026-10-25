<?php

namespace Bernskiold\LaravelPostmark\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Postmark authenticates each webhook with the HTTP Basic Auth credentials
 * of the webhook. This middleware checks these credentials.
 */
class VerifyPostmarkWebhook
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $username = config('postmark.webhooks.username');
        $password = config('postmark.webhooks.password');
        $log = Log::channel(config('postmark.log_channel'));

        if (! $username || ! $password) {
            $log->error('Postmark webhook credentials not configured');

            return new Response('Webhook credentials not configured', 500);
        }

        $providedUsername = (string) $request->getUser();
        $providedPassword = (string) $request->getPassword();

        if (! hash_equals((string) $username, $providedUsername) || ! hash_equals((string) $password, $providedPassword)) {
            $log->warning('Invalid Postmark webhook credentials', [
                'ip' => $request->ip(),
                'provided_username' => $providedUsername,
            ]);

            return new Response('Unauthorized', 401);
        }

        return $next($request);
    }
}
