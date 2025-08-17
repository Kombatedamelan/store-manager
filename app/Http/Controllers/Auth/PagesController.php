<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderDetail;
use \App\Models\ProductDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



class PagesController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
    
        // 🧮 Ventes du jour / hier
        $totalVentesJour = Order::whereDate('created_at', $today)->sum('total');
        $totalVentesHier = Order::whereDate('created_at', $yesterday)->sum('total');
        $variationVentesJour = $this->calculateVariation($totalVentesJour, $totalVentesHier);
    
        $nombreVentesJour = Order::whereDate('created_at', $today)->count();
        $nombreVentesHier = Order::whereDate('created_at', $yesterday)->count();
        $variationNombreVentes = $this->calculateVariation($nombreVentesJour, $nombreVentesHier);
    
        // 🧾 Total transactions (ventes + ajout stock)
        $nombreAjoutsStock = Product::whereDate('updated_at', $today)->count();  // Nombre d'ajouts de stock
        $totalTransactions = $nombreVentesJour + $nombreAjoutsStock;  // Ventes + Ajouts stock
        $totalTransactionsHier = $nombreVentesHier + Product::whereDate('updated_at', $yesterday)->count();  // Transactions d'hier
        $variationTransactions = $this->calculateVariation($totalTransactions, $totalTransactionsHier);
    
        // 🔻 Produits en rupture
        $produitsEnRupture = Product::where('qte', '<=', 0)->count();
    
        // 📊 Ventes semaine dernière (graphique)
        $start = Carbon::now()->subWeek()->startOfWeek();
        $end = Carbon::now()->subWeek()->endOfWeek();
        $ventesSemaineDerniere = [];
    
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $total = Order::whereDate('created_at', $date)->sum('total');
            $ventesSemaineDerniere[] = [
                'jour' => $date->translatedFormat('l'), // ex: Lundi
                'total' => $total
            ];
        }
    
        // 🌟 Produits populaires
        $produitsPopulaires = OrderDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as total_vendus'),
                DB::raw('SUM(quantity * price) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_vendus')
            ->with(['product.category'])
            ->take(5)
            ->get()
            ->map(function ($detail) {
                return [
                    'produit_id' => $detail->product->id,
                    'nom_produit' => $detail->product->name,
                    'categorie' => $detail->product->category ? $detail->product->category->name : null,
                    'quantite_vendue' => $detail->total_vendus,
                    'revenue_total' => $detail->total_revenue,
                ];
            });
    
        return response()->json([
            'total_ventes_du_jour' => [
                'valeur' => $totalVentesJour,
                'variation' => $variationVentesJour
            ],
            'nombre_total_ventes' => [
                'valeur' => $nombreVentesJour,
                'variation' => $variationNombreVentes
            ],
            'total_transactions' => [
                'valeur' => $totalTransactions,
                'variation' => $variationTransactions
            ],
            'produits_en_rupture' => $produitsEnRupture,
            'ventes_semaine_derniere' => $ventesSemaineDerniere,
            'produits_populaires' => $produitsPopulaires,
        ]);
    }
    

    /**
     * Calcule le pourcentage de variation entre deux valeurs.
     */
    private function calculateVariation($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }

        return round((($today - $yesterday) / $yesterday) * 100, 2);
    }

    public function suplierIndex(){
        $supliers = Fournisseur::all();
        return response()->json($supliers);
    }


    public function popularProducts()
    {
        $products = Product::select(
                'products.id',
                'products.name',
                'products.price',
                'categories.name as category',
                DB::raw('SUM(order_details.quantity) as sold'),
                DB::raw('SUM(order_details.quantity * order_details.price) as revenue')
            )
            ->join('order_details', 'products.id', '=', 'order_details.product_id')
            ->join('categories', 'products.categorie_id', '=', 'categories.id')
            ->groupBy('products.id', 'products.name', 'products.price', 'categories.name')
            ->orderByDesc('sold')
            ->take(10)
            ->get();

        return response()->json($products);
    }
    public function weeklySalesSummary()
    {
        $sales = DB::table('orders')
            ->select(
                DB::raw("DAYOFWEEK(created_at) as day"),
                DB::raw("SUM(total) as total")
            )
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(), 
                Carbon::now()->endOfWeek()
            ])
            ->groupBy(DB::raw("DAYOFWEEK(created_at)"))
            ->get();

        $weekDays = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        $formatted = [];

        foreach (range(1, 7) as $dayNumber) {
            $dayName = $weekDays[$dayNumber - 1];
            $daySales = $sales->firstWhere('day', $dayNumber);
            $formatted[] = [
                'name' => $dayName,
                'total' => $daySales ? (int) $daySales->total : 0
            ];
        }

        return response()->json($formatted);
    }


    public function recentTransactions()
    {
        $sales = \App\Models\Order::latest()->take(5)->get()->map(function ($sale) {
            return [
                'id' => $sale->id,
                'type' => 'sale',
                'description' => 'Vente #' . $sale->id,
                'amount' => $sale->total,
                'date' => $sale->created_at,
                'status' => 'complété',
            ];
        });

        $purchases = \App\Models\ProductDetail::latest()->take(5)->get()->map(function ($purchase) {
            return [
                'id' => $purchase->id + 10000, // éviter conflit d'ID avec les ventes
                'type' => 'purchase',
                'description' => 'Réapprovisionnement',
                'amount' => $purchase->price * $purchase->qte,
                'date' => $purchase->created_at,
                'status' => 'complété',
            ];
        });

        $merged = $sales->concat($purchases)->sortByDesc('date')->values()->take(5);

        return response()->json($merged);
    }

    public function getDashboardData()
    {
        // Récupérer les ventes du jour
        $today = Carbon::today();
        $salesToday = Order::whereDate('created_at', $today)->get();

        // Calculer la somme des ventes du jour
        $salesTotal = $salesToday->sum('total');

        // Calculer le nombre de produits vendus (parcourir les OrderDetails)
        $productsSold = $salesToday->flatMap(function ($order) {
            return $order->orderDetails;
        })->sum('quantity');

        // Récupérer les achats récents (sur une période donnée, ici on prend 7 jours)
        $purchases = ProductDetail::whereDate('created_at', '>=', $today->subDays(7))->get();

        // Nombre d'achats
        $purchaseCount =ProductDetail::whereDate('created_at', $today)->count();

        // Nombre de ventes
        $sales = $salesToday->count();

        // Nombre total de transactions (ventes + achats)
        // $totalTransactions = $salesCount ;

        return response()->json([
            'sales_today' => number_format($salesTotal, 2) . ' FCFA', // Somme des ventes du jour
            'products_sold' => $productsSold, // Nombre de produits vendus
            'sales' => $sales, // Nombre total de transactions
        ]);
    }


    public function show()
    { 
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function update(Request $request)
    {
        $user = Auth::user(); // Récupère l'utilisateur connecté

        $request->validate([
            'storeName' => 'nullable|string',
            'storePhone' => 'nullable|string|max:20',
            'storeAddress' => 'nullable|string',
            'currency' => 'nullable|string',
            'adminPhone' => 'nullable|string|max:20',
            'adminPassword' => 'nullable|string',
        ]);

        $user->storeName = $request->storeName ?? $user->storeName;
        $user->storePhone = $request->storePhone ?? $user->storePhone;
        $user->storeAddress = $request->storeAddress ?? $user->storeAddress;
        $user->currency = $request->currency ?? $user->currency;
        $user->adminPhone = $request->adminPhone ?? $user->adminPhone;
        
        if ($request->filled('adminPassword')) {
            $user->adminPassword = Hash::make($request->adminPassword);
        }
        
        $user->save();  // Enregistre les modifications
        // Mise à jour des données dans la base de données
        Log::info('Avant update', ['user' => $user]);



        return response()->json([
            'message' => 'Informations administrateur mises à jour avec succès.',
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    }

}
