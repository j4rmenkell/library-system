<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    // the landing page: 30 random books with covers for the spinning shelf
    public function index(): Response
    {
        $books = Book::with('category')->whereNotNull('cover_path')->inRandomOrder()->take(30)->get();

        return Inertia::render('welcome', compact('books'));
    }
}
