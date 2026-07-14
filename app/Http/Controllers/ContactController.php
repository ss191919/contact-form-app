<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
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

        if (!empty($validated['tag_ids'])) {
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
        //
    }

    /**
     * 完了画面
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
