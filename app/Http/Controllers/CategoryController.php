<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('categories/index', [
            // withCount adds books_count, which the page shows per row
            'categories' => Category::withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        ActivityLogger::log("Added category {$category->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category added.']);

        return to_route('categories.index');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        ActivityLogger::log("Edited category {$category->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category updated.']);

        return to_route('categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Books need a category, so a category in use can't be deleted
        if ($category->books()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => "{$category->name} still has books, so it can't be deleted."]);

            return back();
        }

        $category->delete();

        ActivityLogger::log("Deleted category {$category->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category deleted.']);

        return to_route('categories.index');
    }
}