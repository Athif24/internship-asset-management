<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DivisionController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            ['divisions' => Division::orderBy('id')->get()],
            'Daftar divisi berhasil diambil.'
        );
    }
}
