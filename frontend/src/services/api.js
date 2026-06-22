// Service tập trung các lời gọi từ React sang Laravel API.
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

const getStoredUser = () => {
  try {
    return JSON.parse(localStorage.getItem("user"));
  } catch {
    return null;
  }
};

const adminHeaders = () => {
  const user = getStoredUser();

  // Backend admin kiểm tra email và role trong header trước khi xử lý request.
  return {
    "Content-Type": "application/json",
    "X-Admin-Email": user?.email || "",
    "X-Admin-Role": user?.role || "",
  };
};

const adminUploadHeaders = () => {
  const user = getStoredUser();

  // Upload dùng FormData nên không set Content-Type để trình duyệt tự thêm boundary.
  return {
    "X-Admin-Email": user?.email || "",
    "X-Admin-Role": user?.role || "",
  };
};

// Nhóm API quản trị người dùng.
export const fetchAdminUsers = async () => {
  const response = await fetch(`${API_BASE_URL}/admin/users`, {
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải danh sách người dùng");
  }

  return data;
};

export const updateAdminUserRole = async (sdt, role) => {
  const response = await fetch(`${API_BASE_URL}/admin/users/${sdt}/role`, {
    method: "PATCH",
    headers: adminHeaders(),
    body: JSON.stringify({ role }),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể cập nhật vai trò");
  }

  return data;
};

// Nhóm API quản trị sản phẩm và biến thể.
export const fetchAdminProductMeta = async () => {
  const response = await fetch(`${API_BASE_URL}/admin/products/meta`, {
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải dữ liệu sản phẩm");
  }

  return data;
};

export const fetchAdminProducts = async (filters = {}) => {
  const params = new URLSearchParams();

  if (filters.q) params.set("q", filters.q);
  if (filters.maDanhMuc && filters.maDanhMuc !== "all") {
    params.set("maDanhMuc", filters.maDanhMuc);
  }

  const queryString = params.toString();
  const response = await fetch(
    `${API_BASE_URL}/admin/products${queryString ? `?${queryString}` : ""}`,
    {
      headers: adminHeaders(),
    }
  );
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải danh sách sản phẩm");
  }

  return data;
};

export const uploadAdminProductImage = async (file) => {
  const formData = new FormData();
  formData.append("image", file);

  const response = await fetch(`${API_BASE_URL}/admin/products/upload-image`, {
    method: "POST",
    headers: adminUploadHeaders(),
    body: formData,
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải ảnh lên");
  }

  return data;
};

export const createAdminProduct = async (payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/products`, {
    method: "POST",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể thêm sản phẩm");
  }

  return data;
};

export const updateAdminProduct = async (id, payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/products/${id}`, {
    method: "PUT",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể cập nhật sản phẩm");
  }

  return data;
};

export const deleteAdminProduct = async (id) => {
  const response = await fetch(`${API_BASE_URL}/admin/products/${id}`, {
    method: "DELETE",
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể xóa sản phẩm");
  }

  return data;
};

// Nhóm API quản trị danh mục, hãng sản xuất và thuộc tính.
export const fetchAdminCategories = async () => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/categories`, {
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải danh sách danh mục");
  }

  return data;
};

export const createAdminCategory = async (payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/categories`, {
    method: "POST",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể thêm danh mục");
  }

  return data;
};

export const updateAdminCategory = async (id, payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/categories/${id}`, {
    method: "PUT",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể cập nhật danh mục");
  }

  return data;
};

export const deleteAdminCategory = async (id) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/categories/${id}`, {
    method: "DELETE",
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể xóa danh mục");
  }

  return data;
};

export const fetchAdminManufacturers = async () => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/manufacturers`, {
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải danh sách hãng sản xuất");
  }

  return data;
};

export const createAdminManufacturer = async (payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/manufacturers`, {
    method: "POST",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể thêm hãng sản xuất");
  }

  return data;
};

export const updateAdminManufacturer = async (id, payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/manufacturers/${id}`, {
    method: "PUT",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể cập nhật hãng sản xuất");
  }

  return data;
};

export const deleteAdminManufacturer = async (id) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/manufacturers/${id}`, {
    method: "DELETE",
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể xóa hãng sản xuất");
  }

  return data;
};

export const fetchAdminAttributes = async () => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/attributes`, {
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể tải danh sách thuộc tính");
  }

  return data;
};

export const createAdminAttribute = async (payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/attributes`, {
    method: "POST",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể thêm thuộc tính");
  }

  return data;
};

export const updateAdminAttribute = async (id, payload) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/attributes/${id}`, {
    method: "PUT",
    headers: adminHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể cập nhật thuộc tính");
  }

  return data;
};

export const deleteAdminAttribute = async (id) => {
  const response = await fetch(`${API_BASE_URL}/admin/catalog/attributes/${id}`, {
    method: "DELETE",
    headers: adminHeaders(),
  });
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || "Không thể xóa thuộc tính");
  }

  return data;
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
  fetchAdminUsers,
  updateAdminUserRole,
  fetchAdminProductMeta,
  fetchAdminProducts,
  uploadAdminProductImage,
  createAdminProduct,
  updateAdminProduct,
  deleteAdminProduct,
  fetchAdminCategories,
  createAdminCategory,
  updateAdminCategory,
  deleteAdminCategory,
  fetchAdminManufacturers,
  createAdminManufacturer,
  updateAdminManufacturer,
  deleteAdminManufacturer,
  fetchAdminAttributes,
  createAdminAttribute,
  updateAdminAttribute,
  deleteAdminAttribute,
  formatPrice,
};
