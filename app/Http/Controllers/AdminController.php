<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\brand;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }

    public function brands() {
        $brands = Brand::orderBy('id,DECS')->paginate(10);
        return view('admin.brands',compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }
}
