<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\ProductModel;

class Cart extends BaseController
{
    protected $cartModel;
    protected $cartItemModel;
    protected $productModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $userId = $this->request->user->id;

        $cart = $this->cartModel->where('user_id', $userId)->first();

        if (!$cart) {
            $cartId = $this->cartModel->insert(['user_id' => $userId]);
            $cart = ['id' => $cartId, 'user_id' => $userId];
        }

        $cartItems = $this->cartItemModel->select('cart_items.*, products.name, products.price, products.image_url')
            ->join('products', 'products.id = cart_items.product_id')
            ->where('cart_id', $cart['id'])
            ->findAll();

        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Cart retrieved successfully',
            'data' => [
                'items' => $cartItems,
                'total' => $total
            ]
        ]);
    }

    public function add()
    {
        $userId = $this->request->user->id;

        $rules = [
            'product_id' => 'required|is_not_unique[products.id]',
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validation failed',
                'data' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $cart = $this->cartModel->where('user_id', $userId)->first();
        if (!$cart) {
            $cartId = $this->cartModel->insert(['user_id' => $userId]);
        } else {
            $cartId = $cart['id'];
        }

        $productId = $this->request->getVar('product_id');
        $quantity = (int)$this->request->getVar('quantity');

        $existingItem = $this->cartItemModel->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            $this->cartItemModel->update($existingItem['id'], [
                'quantity' => $existingItem['quantity'] + $quantity
            ]);
        } else {
            $this->cartItemModel->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Product added to cart'
        ]);
    }

    public function update($id = null)
    {
        $userId = $this->request->user->id;
        $cart = $this->cartModel->where('user_id', $userId)->first();

        if (!$cart) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart not found'
            ])->setStatusCode(404);
        }

        $cartItem = $this->cartItemModel->where('id', $id)->where('cart_id', $cart['id'])->first();

        if (!$cartItem) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart item not found'
            ])->setStatusCode(404);
        }

        $rules = [
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validation failed',
                'data' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $this->cartItemModel->update($id, [
            'quantity' => $this->request->getVar('quantity')
        ]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Cart item updated'
        ]);
    }

    public function remove($id = null)
    {
        $userId = $this->request->user->id;
        $cart = $this->cartModel->where('user_id', $userId)->first();

        if (!$cart) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart not found'
            ])->setStatusCode(404);
        }

        $cartItem = $this->cartItemModel->where('id', $id)->where('cart_id', $cart['id'])->first();

        if (!$cartItem) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart item not found'
            ])->setStatusCode(404);
        }

        $this->cartItemModel->delete($id);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Cart item removed'
        ]);
    }
}