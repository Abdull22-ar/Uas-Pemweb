<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetugasLokasiController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        return view('admin.petugas.lokasi', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'lokasi'    => 'nullable|string|max:255',
        ]);

        auth()->user()->update($request->only(['latitude', 'longitude', 'lokasi']));

        return redirect()->route('admin.lokasi.edit')
            ->with('success', 'Lokasi Anda berhasil diperbarui.');
    }
}