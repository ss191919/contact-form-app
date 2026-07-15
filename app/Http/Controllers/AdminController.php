<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::with(['category', 'tags'])
            ->when($request->keyword, function ($query) use ($request) {
                $keyword = $request->keyword;

                $query->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($request->gender, function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->date, function ($query) use ($request) {
                $query->whereDate('created_at', $request->date);
            })
            ->paginate(7);


        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact(
            'contacts',
            'categories',
            'tags'
        ));
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }
}
