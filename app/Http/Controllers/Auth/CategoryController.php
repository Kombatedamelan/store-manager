<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorie;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categorie::all();
        return response()->json($categories);
    }

    //  Créer une nouvelle catégorie
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $categorie = Categorie::create([
            'name' => $request->name,
        ]);

        return response()->json($categorie, 201);
    }

    //  Afficher une catégorie par ID
    public function show($id)
    {
        $categorie = Categorie::findOrFail($id);
        return response()->json($categorie);
    }

    //  Mettre à jour une catégorie
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $categorie = Categorie::findOrFail($id);
        $categorie->update([
            'name' => $request->name,
        ]);

        return response()->json($categorie);
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        Categorie::destroy($id);
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}
