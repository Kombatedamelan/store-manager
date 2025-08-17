<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fournisseur;




class FournisseurController extends Controller
{
    public function index()
    {
        $suppliers = Fournisseur::all();
        return response()->json($suppliers);
    }
    

    //  Créer une nouvelle catégorie
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $supplier = Fournisseur::create([
            'name' => $request->name,
        ]);

        return response()->json($supplier, 201);
    }

    //  Afficher une catégorie par ID
    public function show($id)
    {
        $supplier = Fournisseur::findOrFail($id);
        return response()->json($supplier);
    }

    //  Mettre à jour une catégorie
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $supplier = Fournisseur::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
        ]);

        return response()->json($supplier);
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        Fournisseur::destroy($id);
        return response()->json(['message' => 'Fournisseur supprimée']);
    }
}
