<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    // The landing page: a shelf of covers for the Infinite Menu
    public function __invoke(): Response
    {
        return Inertia::render('welcome', [
            'books' => Book::with('category')->whereNotNull('cover_path')->inRandomOrder()->take(30)->get(),
        ]);
    }
}
