<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * お問い合わせ入力画面表示
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * 入力内容確認画面表示
     */
    public function confirm(ContactRequest $request)
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);

        $tags = collect();

        if (! empty($validated['tag_ids'])) {
            $tags = Tag::whereIn('id', $validated['tag_ids'])->get();
        }

        return view('contact.confirm', compact(
            'validated',
            'category',
            'tags'
        ));
    }

    /**
     * 保存処理
     */
    public function store(ContactRequest $request)
    {
        $validated = $request->validated();

        // contactsテーブルへ保存
        $contact = Contact::create([
            'category_id' => $validated['category_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'detail' => $validated['detail'],
        ]);

        // contact_tagテーブルへ保存
        if (! empty($validated['tag_ids'])) {
            $contact->tags()->attach($validated['tag_ids']);
        }

        // 完了画面へ
        return redirect()->route('contact.thanks');
    }

    /**
     * 完了画面
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
