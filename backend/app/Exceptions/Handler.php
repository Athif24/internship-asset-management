<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Semua endpoint API/XHR mengembalikan envelope { success, message, data, error }.
     * Permintaan halaman (HTML) ditangani terpisah agar sidebar Blade tetap tampil.
     */
    public function render($request, Throwable $e): Response
    {
        if (! $this->wantsEnvelope($request)) {
            return $this->renderWebError($request, $e);
        }

        if ($e instanceof ValidationException) {
            // AST-01: duplicate asset_code terdeteksi lebih awal oleh FormRequest.
            if (isset($e->errors()['assets_code'])) {
                return ApiResponse::error(
                    'Kode aset sudah digunakan.',
                    'AST-01',
                    $e->errors(),
                    409
                );
            }

            return ApiResponse::error(
                'Input tidak valid.',
                'VALIDATION_ERROR',
                $e->errors(),
                422
            );
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error('Belum login.', 'UNAUTHENTICATED', null, 401);
        }

        if ($e instanceof AuthorizationException) {
            return ApiResponse::error('Akses tidak sah.', 'FORBIDDEN', null, 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error('Data tidak ditemukan.', 'NOT_FOUND', null, 404);
        }

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();

            if ($status === 403) {
                return ApiResponse::error('Akses tidak sah.', 'FORBIDDEN', null, 403);
            }

            if ($status === 404) {
                return ApiResponse::error('Endpoint atau data tidak ditemukan.', 'NOT_FOUND', null, 404);
            }
        }

        if ($e instanceof QueryException) {
            return $this->renderQueryException($e);
        }

        $this->report($e);

        return ApiResponse::error('Terjadi kesalahan pada server.', 'INTERNAL_ERROR', null, 500);
    }

    /**
     * Guard web yang tidak terautentikasi diarahkan ke /login.
     * Endpoint /api tetap memakai envelope 401 seperti sebelumnya.
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        if ($this->wantsEnvelope($request)) {
            return ApiResponse::error('Belum login.', 'UNAUTHENTICATED', null, 401);
        }

        return redirect()->guest('/login');
    }

    private function wantsEnvelope($request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    /**
     * Panel error untuk permintaan halaman: layouts.app dipakai agar sidebar tetap tampil.
     */
    private function renderWebError($request, Throwable $e): Response
    {
        if ($e instanceof ValidationException) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->guest('/login');
        }

        $status = 500;
        $title = 'Terjadi kesalahan server';
        $message = 'Terjadi kesalahan pada server. Coba lagi beberapa saat.';

        if ($e instanceof ModelNotFoundException || ($e instanceof HttpException && $e->getStatusCode() === 404)) {
            $status = 404;
            $title = 'Data tidak ditemukan';
            $message = 'Data yang diminta tidak tersedia.';
        } elseif ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() === 403)) {
            $status = 403;
            $title = 'Akses ditolak';
            $message = 'Akun Anda tidak memiliki akses ke halaman ini.';
        }

        if ($status === 500) {
            $this->report($e);
        }

        return response()->view('errors.panel', [
            'page' => 'error',
            'title' => $title,
            'status' => $status,
            'message' => $message,
        ], $status);
    }

    /**
     * Terjemahkan error stored procedure / constraint MySQL ke kode AST.
     */
    private function renderQueryException(QueryException $e): Response
    {
        $driverMessage = $e->errorInfo[2] ?? '';
        $haystack = $driverMessage.' '.$e->getMessage();

        if (str_contains($haystack, 'AST-03')) {
            return ApiResponse::error('Aset sedang dipinjam.', 'AST-03', $driverMessage, 409);
        }

        if (str_contains($haystack, 'AST-04')) {
            return ApiResponse::error('Aset sedang dalam maintenance.', 'AST-04', $driverMessage, 409);
        }

        if (str_contains($haystack, 'AST-06')) {
            return ApiResponse::error('Aset tidak sedang dipinjam.', 'AST-06', $driverMessage, 409);
        }

        if (str_contains($haystack, 'AST-07')) {
            return ApiResponse::error('Hanya peminjam yang boleh mengembalikan aset.', 'AST-07', $driverMessage, 403);
        }

        if (str_contains($haystack, 'ASSET_NOT_FOUND') || str_contains($haystack, 'USER_NOT_FOUND')) {
            return ApiResponse::error('Data tidak ditemukan.', 'NOT_FOUND', $driverMessage, 404);
        }

        if (str_contains($haystack, '1062') && str_contains($haystack, 'assets_code')) {
            return ApiResponse::error('Kode aset sudah digunakan.', 'AST-01', $driverMessage, 409);
        }

        if (str_contains($haystack, '1205') || str_contains($haystack, 'Lock wait timeout')) {
            return ApiResponse::error(
                'Aset sedang diproses, silakan coba lagi.',
                'AST-09',
                $driverMessage,
                409
            );
        }

        if (str_contains($haystack, '1451') || str_contains($haystack, 'foreign key constraint fails')) {
            return ApiResponse::error(
                'Data masih digunakan oleh data lain.',
                'FK_CONSTRAINT',
                $driverMessage,
                409
            );
        }

        $this->report($e);

        return ApiResponse::error('Gagal menjalankan operasi database.', 'DATABASE_ERROR', $driverMessage, 500);
    }
}
