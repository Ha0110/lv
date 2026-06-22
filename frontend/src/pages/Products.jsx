import { useState, useMemo, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import ProductCard from "../components/ProductCard";
import { fetchProducts, fetchCategories } from "../services/api";

export default function Products() {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [searchParams, setSearchParams] = useSearchParams();
  const initialCat = searchParams.get("cat") || "all";
  const [activeCategory, setActiveCategory] = useState(initialCat);
  const [sortBy, setSortBy] = useState("default");

  useEffect(() => {
    const loadProducts = async () => {
      const data = await fetchProducts();
      setProducts(data);
    };
    const loadCategories = async () => {
      const data = await fetchCategories();
      setCategories([{ id: "all", name: "Tất cả" }, ...data]);
    };
    loadProducts();
    loadCategories();
  }, []);

  const filtered = useMemo(() => {
    let list = [...products];
    if (activeCategory && activeCategory !== "all") {
      list = products.filter((p) => {
        return p.category && p.category.toLowerCase() === activeCategory.toLowerCase();
      });
    }

    if (sortBy === "price-asc") list.sort((a, b) => a.price - b.price);
    if (sortBy === "price-desc") list.sort((a, b) => b.price - a.price);
    if (sortBy === "name") list.sort((a, b) => a.name.localeCompare(b.name));

    return list;
  }, [products, activeCategory, sortBy]);

  const handleCategoryChange = (catId) => {
    const categoryName = categories.find((c) => c.id === catId || c.name === catId)?.name || catId;
    setActiveCategory(categoryName === "Tất cả" ? "all" : categoryName);
    if (categoryName === "Tất cả") {
      setSearchParams({});
    } else {
      setSearchParams({ cat: categoryName });
    }
  };

  return (
    <>
      <section className="page-header">
        <div className="container">
          <h1>Sản phẩm</h1>
          <p>Linh kiện máy tính chính hãng - Giá tốt nhất</p>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <div className="products-toolbar">
            <div className="category-filter">
              {categories.length > 0 ? (
                categories.map((cat) => (
                  <button
                    key={cat.id}
                    className={`cat-btn ${activeCategory === (cat.name === "Tất cả" ? "all" : cat.name) ? "active" : ""}`}
                    onClick={() => handleCategoryChange(cat.id)}
                  >
                    {cat.name}
                  </button>
                ))
              ) : (
                <p>Đang tải danh mục...</p>
              )}
            </div>
            <div className="sort-filter">
              <select value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
                <option value="default">Sắp xếp mặc định</option>
                <option value="price-asc">Giá: Thấp → Cao</option>
                <option value="price-desc">Giá: Cao → Thấp</option>
                <option value="name">Tên A-Z</option>
              </select>
            </div>
          </div>

          {filtered.length > 0 ? (
            <div className="products-grid">
              {filtered.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <p className="no-products">Không tìm thấy sản phẩm nào.</p>
          )}
        </div>
      </section>
    </>
  );
}
