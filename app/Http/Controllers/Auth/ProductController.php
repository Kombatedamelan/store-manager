<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDetail;
use Carbon\Carbon;



class ProductController extends Controller
{
    

    public function index(Request $request)
    {
        $query = Product::with('category');

        //  Filtre par nom du produit (recherche partielle)
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        //  Filtre par stock
        if ($request->has('stock')) {
            switch ($request->stock) {
                case 'low':
                    $query->where('stock', '>', 0)->where('stock', '<=', 10);
                    break;
                case 'out':
                    $query->where('stock', '=', 0);
                    break;
            }
        }

        //  Filtre par catégorie
        if ($request->has('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        $products = $query->get();

        return response()->json($products);
    }


    //  Ajouter un produit
    public function ProductStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'qte' => 'required|integer',
            'ref' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);
         // Recharger avec la relation category
        $product->load('category');

        return response()->json([
            'message' => 'Produit ajouté avec succès.',
            'product' => $product
        ], 201);
    }

    //  Afficher un produit
    public function show($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        return response()->json($product);
    }

    //  Modifier un produit
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'qte' => 'sometimes|integer',
            'ref' => 'nullable|string',
            'categorie_id' => 'sometimes|exists:categories,id',
        ]);

        $product->update($validated);
        $product->load('category'); // Important

        return response()->json([
            'message' => 'Produit mis à jour avec succès.',
            'product' => $product
        ]);
    }

    //  Supprimer un produit
    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès.']);
    }

    
    public function purchase(Request $request)
    {
        $query = ProductDetail::with('supplier', 'product');

        //  Filtre fournisseur
        if ($request->has('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        //  Filtre par date
        if ($request->date === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($request->date === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($request->has('from') && $request->has('to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay()
            ]);
        }

        $details = $query->latest()->get();

        // Transformer les données
        $data = $details->map(function ($detail) {
            return [
                'date' => $detail->created_at->format('d/m/Y H:i:s'),
                'supplier' => $detail->supplier->name ?? '—',
                'produit' => $detail->product->name ?? '—',
                'quantity' => $detail->qte,
                'total' => $detail->qte * $detail->price,
                'status' => $detail->status,
            ];
        });

        // Totaux
        $totalAchats = $details->count();
        $sommeTotale = $details->sum(fn($d) => $d->qte * $d->price);

        return response()->json([
            'filters' => [
                'fournisseur_id' => $request->fournisseur_id ?? null,
                'date' => $request->date ?? 'all',
                'from' => $request->from ?? null,
                'to' => $request->to ?? null,
            ],
            'achats' => $data,
            'total_achats' => $totalAchats,
            'somme_totale' => $sommeTotale,
        ]);
    }


    public function addStock(Request $request, $id)
    {
        $request->validate([
            'qte' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',  
            'user_id' => 'required|exists:fournisseurs,id'  
        ]);

        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $product->qte += $request->qte;
        $product->save();

        // Enregistrement dans la table product_details
        ProductDetail::create([
            'product_id' => $product->id,
            'user_id' => $request->user_id,
            'qte' => $request->qte,
            "price"=>$request->price,
        ]);

        return response()->json([
            'message' => 'Stock mis à jour avec succès.',
            'product' => $product
        ]);
    }


    //filtre en fonction du nom du fournisseur
    public function stockByFournisseur($id)
    {
        $details = ProductDetail::with('fournisseur', 'product')
            ->where('fournisseur_id', $id) // ou 'user_id' selon ta colonne
            ->latest()
            ->get();

        if ($details->isEmpty()) {
            return response()->json(['message' => 'Aucun approvisionnement trouvé pour ce fournisseur.'], 404);
        }

        $data = $details->map(function ($detail) {
            return [
                'date' => $detail->created_at->format('d/m/Y H:i:s'),
                'fournisseur' => $detail->supplier->name ?? '—',
                'produit' => $detail->product->name ?? '—',
                'quantite' => $detail->qte,
                'total' => $detail->qte * $detail->price,
                'status' => $detail->status,
            ];
        });

        return response()->json($data);
    }



}
