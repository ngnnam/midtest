<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Spatie\Image\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Product;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }

    public function brands() {
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }
    public function add_brand(){
        return view('admin.brand-add');
    }

    public function brand_store(Request $request){
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:brands,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);
    
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
    
        if ($request->hasFile('image')) { //ktra xem file anh exits k
            $image = $request->file('image');
            $file_name = Carbon::now()->timestamp . '.' . $image->extension();
            $image->move(public_path('uploads/brands'), $file_name);
    
            $brand->image = $file_name;
        }
    
        $brand->save();
    
        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully');
    }
    
    public function brand_edit($id){
        $brand = Brand::find($id);
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found');
        }
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request){
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:brands,slug,' . $request->id,
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = Brand::find($request->id);
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found');
        }
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        if ($request->hasFile('image')) { 
            $image = $request->file('image');
            $file_name = Carbon::now()->timestamp . '.' . $image->extension();
            $oldImagePath = public_path('uploads/brands/') . $brand->image; //xoa anh cu
            if (File::exists($oldImagePath)) {  
                File::delete($oldImagePath);
            } 
            // Lưu ảnh mới
            $image->move(public_path('uploads/brands'), $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully');
    }
    public function generateBrandThumbnail($image, $imageName){
        $destinationPath = public_path('uploads/brands');
    
        // Tạo thư mục nếu chưa có
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
    
        // Kiểm tra file ảnh hợp lệ
        if (!$image->isValid()) {
            return false;
        }
        Image::load($image->getRealPath())
            ->width(124)
            ->height(124)
            ->save($destinationPath . '/' . $imageName);
    }
    
    public function brand_delete($id) {
        $brand = Brand::find($id);
    
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found');
        }
        if ($brand->image && File::exists(public_path('uploads/brands/' . $brand->image))) {
            File::delete(public_path('uploads/brands/' . $brand->image));
        }
    
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully');
    }
    
    public function categories() {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }
    public function add_category(){
        return view('admin.categories-add');
    }
    
    public function category_store(Request $request){
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:categories,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);
    
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
    
        if ($request->hasFile('image')) { 
            $image = $request->file('image');
            $file_name = Carbon::now()->timestamp . '.' . $image->extension();
            $image->move(public_path('uploads/categories'), $file_name);
    
            $category->image = $file_name;
        }
    
        $category->save();
    
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully');
    }

    public function category_edit($id) {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }
    
    public function category_update(Request $request) {
        $request->validate([
            'id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $request->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
        ]);
    
        $category = Category::findOrFail($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;
    
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->image = $imagePath;
        }
    
        $category->save();
        
        return redirect()->route('admin.categories')->with('status', 'Category updated successfully!');
    }
    
    public function generateCategoryThumbnail($image, $imageName){
        $destinationPath = public_path('uploads/categories');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        if (!$image->isValid()) {
            return false;
        }
        Image::load($image->getRealPath())
            ->width(124)
            ->height(124)
            ->save($destinationPath . '/' . $imageName);
    }

    public function category_delete($id) {
        $category = Category::find($id);
    
        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found');
        }
        if ($category->image && File::exists(public_path('uploads/categories/' . $category->image))) {
            File::delete(public_path('uploads/categories/' . $category->image));
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully');
    }
    
    public function products() {
        $products = Product::orderBy('id','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function product_add() {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }
    
    public function product_store(Request $request) {
        $request->validate([
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
            'brand_id' => 'required',
            'category_id' => 'required',
            'images.*' => ['nullable', 'mimes:png,jpg,jpeg', 'max:2048']
        ]);
    
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name); // Nếu muốn nhập slug thì sửa lại ở đây và thêm validate
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->brand_id = $request->brand_id;
        $product->category_id = $request->category_id;
    
        $current_timestamp = Carbon::now()->timestamp;
    
        // Xử lý ảnh đại diện
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->generateProductThumbnail($image, $imageName, 'uploads/products/thumbnails', 400, 400);
            $product->image = $imageName;
        }
    
        // Xử lý ảnh gallery
        $gallery_arr = [];
        if ($request->hasFile('images')) {
            $allowedfile_extension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            $counter = 1;
    
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                if (in_array($gextension, $allowedfile_extension)) {
                    $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->generateProductThumbnail($file, $gfileName, 'uploads/products/gallery', 800, 800); // Gallery có thể để kích thước khác
                    $gallery_arr[] = $gfileName;
                    $counter++;
                }
            }
        }
    
        if (!empty($gallery_arr)) {
            $product->gallery_images = implode(",", $gallery_arr);
        }
    
        $product->save();
    
        return redirect()->route('admin.products')->with('status', 'Product has been added successfully');
    }
    
    
    public function generateProductThumbnail($image, $imageName, $folderPath, $width = 400, $height = 400) {
        $destinationPath = public_path($folderPath);
    
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
    
        if (!$image->isValid()) {
            return false;
        }
    
        Image::load($image->getRealPath())
            ->width($width)
            ->height($height)
            ->save($destinationPath . '/' . $imageName);
    }
}    