// Frontend API service to call Laravel backend
const API_BASE_URL = import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api";

export const fetchProducts = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/products`);
    if (!response.ok) throw new Error("Failed to fetch products");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Fetch products error:", error);
    return [];
  }
};

export const fetchProductById = async (id) => {
  try {
    const response = await fetch(`${API_BASE_URL}/products/${id}`);
    if (!response.ok) throw new Error("Failed to fetch product");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Fetch product error:", error);
    return null;
  }
};

export const fetchCategories = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/categories`);
    if (!response.ok) throw new Error("Failed to fetch categories");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Fetch categories error:", error);
    return [];
  }
};

export const fetchProductsByCategory = async (categoryName) => {
  try {
    const response = await fetch(`${API_BASE_URL}/products?category=${categoryName}`);
    if (!response.ok) throw new Error("Failed to fetch products by category");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Fetch products by category error:", error);
    return [];
  }
};

export const searchProducts = async (query) => {
  try {
    const response = await fetch(`${API_BASE_URL}/search?q=${query}`);
    if (!response.ok) throw new Error("Failed to search products");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Search products error:", error);
    return [];
  }
};

export const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN").format(price) + "₫";
};

export default {
  fetchProducts,
  fetchProductById,
  fetchCategories,
  fetchProductsByCategory,
  searchProducts,
  formatPrice,
};
