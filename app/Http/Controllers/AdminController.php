<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // 一覧
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

    // 詳細
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    // お問い合わせ削除
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect('/admin');
    }

    // タグ追加
    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:tags,name'
            ],
        ]);

        Tag::create([
            'name' => $request->name
        ]);

        return redirect('/admin');

    }

    // タグ編集画面表示
    public function editTag(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    // タグ更新処理
    public function updateTag(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => [
                'required', 'string',
                'max:50',
                Rule::unique('tags', 'name')->ignore($tag->id)
            ],
        ]);

        $tag->update([
            'name' => $request->name
        ]);

        return redirect('/admin');
    }

    // タグ削除処理
    public function destroyTag(Tag $tag)
    {
        $tag->delete();
        return redirect('/admin');
    }
}
