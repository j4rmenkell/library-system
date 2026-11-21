<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    // show all categories, A-Z
    public function index(): Response
    {
        // withCount gives me books_count so the page can show how many books each one has
        $categories = Category::withCount('books')->orderBy('name')->get();

        return Inertia::render('categories/index', compact('categories'));
    }

    // save a new category
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::create($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Added category {$category->name}"]);

        return redirect()->route('categories.index')->with('success', 'Category added.');
    }

    // update a category
    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validatedData = $request->validate([
            // ignore this category's own name when checking unique
            'name' => 'required|string|max:100|unique:categories,name,'.$id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Edited category {$category->name}"]);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    // delete a category
    public function destroy(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        // every book needs a category, so I can't delete one that's still in use
        if ($category->books()->exists()) {
            return back()->with('error', "{$category->name} still has books, so it can't be deleted.");
        }

        $category->delete();

        Log::create(['user_id' => auth()->id(), 'action' => "Deleted category {$category->name}"]);

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
