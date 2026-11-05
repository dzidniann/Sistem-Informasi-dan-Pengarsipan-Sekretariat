<?php

namespace App\Http\Controllers;

class LegalNotulensiController extends LegalActivityController
{
    protected bool $minutes = true;

    public function index(\Illuminate\Http\Request $request)
    {
        return view(
            'legal.notulensi.index',
            array_merge(parent::form()->getData(), parent::index($request)->getData()),
        );
    }

    public function create()
    {
        return $this->index(request());
    }

    protected function form($record = null)
    {
        return view('legal.notulensi.form', parent::form($record)->getData());
    }

    public function show($id)
    {
        return view('legal.notulensi.show', parent::show($id)->getData());
    }
}
