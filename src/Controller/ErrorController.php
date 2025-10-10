<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment as Twig;

final class ErrorController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function show(\Throwable $exception): Response
    {
        $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        // Log 5xx errors for debugging
        if ($status >= 500) {
            $this->logger->error('Server error', [
                'status' => $status,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        // Try specific template first
        $candidates = [
            "bundles/TwigBundle/Exception/error{$status}.html.twig",
            'bundles/TwigBundle/Exception/error.html.twig',
        ];

        foreach ($candidates as $tpl) { // We don't have templates for all pages
            if ($this->twig->getLoader()->exists($tpl)) {
                return new Response(
                    $this->twig->render($tpl, [
                        'status_code' => $status,
                        'status_text' => Response::$statusTexts[$status] ?? 'Error', // Fallback for unknown codes
                    ]),
                    $status,
                    ['Cache-Control' => $status >= 500 ? 'no-store' : 'no-cache']
                );
            }
        }

        // Ultimate fallback if no template exists
        return new Response('An error occurred.', $status);
    }
}
