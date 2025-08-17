<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProductController; 
use App\Http\Controllers\Auth\OrderController; 
use App\Http\Controllers\Auth\CategoryController; 
use App\Http\Controllers\Auth\PagesController; 
use App\Http\Controllers\Auth\FournisseurController; 




// Authentification
Route::post('/login', [LoginController::class, 'login']);

// Routes protégées (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/', [PagesController::class, 'index']);
    Route::get('/products/popular', [PagesController::class, 'popularProducts']);
    Route::get('/weekly-sales', [PagesController::class, 'weeklySalesSummary']);
    Route::get('/recent-transactions', [PagesController::class, 'recentTransactions']);
    Route::get('/dashboard', [PagesController::class, 'getDashboardData']);
    Route::get('/user/settings', [PagesController::class, 'show']);
    Route::post('/user/settings', [PagesController::class, 'update']);
    
    
    
    // Produits publics
    Route::post('/products', [ProductController::class, 'ProductStore']);
    Route::get('/products', [ProductController::class, 'index']);
    
    Route::get('/purchase', [ProductController::class, 'purchase']);
    
    
    // Commandes
    Route::get('/orders', [OrderController::class, 'index']);         
    Route::post('/orders', [OrderController::class, 'store']);      
    Route::get('/orders/{id}', [OrderController::class, 'show']);      
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']); 
    Route::get('/orders/{id}/pdf', [OrderController::class, 'downloadPdf']); 
    
    //les categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    //les fournisseurs
    Route::get('/suppliers', [FournisseurController::class, 'index']);
    Route::post('/suppliers', [FournisseurController::class, 'store']);
    Route::get('/suppliers/{id}', [FournisseurController::class, 'show']);
    Route::put('/suppliers/{id}', [FournisseurController::class, 'update']);
    Route::delete('/suppliers/{id}', [FournisseurController::class, 'destroy']);
    
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::post('/products/{id}/add-stock', [ProductController::class, 'addStock']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user/setting', [PagesController::class, 'show']);
    Route::put('/user/setting', [PagesController::class, 'update']);


    Route::get('auth/me', [PagesController::class, 'me']);
});
