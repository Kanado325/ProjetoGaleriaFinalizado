<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    
public function index(Request $request)
{
    $search = $request->query('search');

    $publications = Publication::with(['user', 'likes'])
        ->when($search, function ($query, $search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        })
        ->latest()
        ->get()
        ->map(function ($pub) {
            return [
                'id' => $pub->id,
                'user_id' => $pub->user_id,
                'photographer_id' => $pub->user_id,
                'title' => $pub->title,
                'location' => $pub->location,
                'photographer' => $pub->user->username
                    ?? $pub->user->name
                    ?? 'Desconhecido',
                'image_url' => asset('storage/' . $pub->image_path),
                'likes_count' => $pub->likes->count(),
                'is_liked' => Auth::check()
                    ? $pub->likes->contains('user_id', Auth::id())
                    : false,
            ];
        });

        return response()->json($publications);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'fotografo') {
            return response()->json([
                'message' => 'Apenas fotógrafos podem cadastrar fotos.'
            ], $user ? 403 : 401);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:4096'
            ],
        ]);

        $imagePath = $request->file('image')
            ->store('photos', 'public');

        $photo = Publication::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'location' => $validated['location'],
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto publicada com sucesso!',
            'photo' => $photo,
        ], 201);
    }


public function update(Request $request, Publication $publication)
{
    abort_unless(
        auth()->user()->role === 'fotografo'
        && $publication->user_id === auth()->id(),
        403
    );

    $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'location' => ['required', 'string', 'max:255'],
        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
    ]);

    $publication->title = $validated['title'];
    $publication->location = $validated['location'];

    if ($request->hasFile('image')) {
        $newPath = $request->file('image')->store('photos', 'public');

        if ($publication->image_path) {
            Storage::disk('public')->delete($publication->image_path);
        }

        $publication->image_path = $newPath;
    }

    $publication->save();

    return response()->json([
        'message' => 'Publicação atualizada com sucesso.',
    ]);
}

    public function destroy(Publication $publication)
    {
        abort_unless(
            auth()->user()->role === 'fotografo'
            && $publication->user_id === auth()->id(),
            403
        );

        if ($publication->image_path) {
            Storage::disk('public')->delete($publication->image_path);
        }

        $publication->delete();

        return response()->json([
            'message' => 'Publicação excluída com sucesso.',
        ]);
    }

}
