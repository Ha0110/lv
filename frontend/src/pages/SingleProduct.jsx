import { useParams, Link } from "react-router-dom";
import { useState, useEffect } from "react";
import { fetchProductById, fetchProducts, formatPrice } from "../services/api";

export default function SingleProduct() {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("details");
  const [quantity, setQuantity] = useState(1);
  const [related, setRelated] = useState([]);

  useEffect(() => {
    const loadProduct = async () => {
      setLoading(true);
      const data = await fetchProductById(id);
      setProduct(data);
      setLoading(false);
    };

    loadProduct();
  }, [id]);

  useEffect(() => {
    if (product) {
      const loadRelated = async () => {
        const allProducts = await fetchProducts();
        const relatedProducts = allProducts
          .filter((p) => p.category === product.category && p.id !== product.id)
          .slice(0, 4);
        setRelated(relatedProducts);
      };
      loadRelated();
    }
  }, [product]);

  if (loading) {
    return (
      <section className="section">
        <div className="container text-center">
          <h2>Đang tải sản phẩm...</h2>
        </div>
      </section>
    );
  }

  if (!product) {
    return (
      <section className="section">
        <div className="container text-center">
          <h2>Không tìm thấy sản phẩm</h2>
          <Link to="/products" className="btn btn-primary" style={{ marginTop: "1rem" }}>
            Quay lại cửa hàng
          </Link>
        </div>
      </section>
    );
  }

  const tabs = [
    { id: "details", label: "Chi tiết" },
    { id: "specs", label: "Thông số kỹ thuật" },
  ];

  return (
    <>
      <section className="page-header">
        <div className="container">
          <h1>{product.name}</h1>
          <p>{product.category}</p>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <div className="single-product-layout">
            <div className="single-product-image">
              <img src={product.image} alt={product.name} />
            </div>

            <div className="single-product-info">
              <span className="product-category-badge">{product.category}</span>
              <h2>{product.name}</h2>

              <div className="single-price">
                <span className="price-current">{formatPrice(product.price)}</span>
                {product.oldPrice && (
                  <span className="price-old">{formatPrice(product.oldPrice)}</span>
                )}
                {product.oldPrice && (
                  <span className="price-discount">
                    -{Math.round(((product.oldPrice - product.price) / product.oldPrice) * 100)}%
                  </span>
                )}
              </div>

              <p className="product-desc">{product.description}</p>

              <div className="quantity-row">
                <label>Số lượng:</label>
                <div className="quantity-control">
                  <button onClick={() => setQuantity(Math.max(1, quantity - 1))}>-</button>
                  <span>{quantity}</span>
                  <button onClick={() => setQuantity(quantity + 1)}>+</button>
                </div>
              </div>

              <button className="btn btn-primary btn-lg">Thêm vào giỏ hàng</button>
            </div>
          </div>

          <div className="product-tabs">
            <div className="tabs-nav">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  className={activeTab === tab.id ? "active" : ""}
                  onClick={() => setActiveTab(tab.id)}
                >
                  {tab.label}
                </button>
              ))}
            </div>
            <div className="tabs-content">
              {activeTab === "details" && (
                <div className="tab-pane">
                  <p>{product.description}</p>
                  <p>
                    Tất cả sản phẩm tại TechZone đều là hàng chính hãng với chế độ bảo hành
                    đầy đủ. Chúng tôi cam kết đổi trả trong 7 ngày nếu sản phẩm có lỗi từ
                    nhà sản xuất.
                  </p>
                  <ul>
                    <li>Hàng chính hãng 100%</li>
                    <li>Bảo hành từ 12-36 tháng</li>
                    <li>Giao hàng nhanh toàn quốc</li>
                    <li>Hỗ trợ lắp đặt miễn phí</li>
                  </ul>
                </div>
              )}

              {activeTab === "specs" && (
                <div className="tab-pane">
                  <table className="specs-table">
                    <tbody>
                      {Object.entries(product.specs).map(([key, val]) => (
                        <tr key={key}>
                          <td className="spec-key">{key}</td>
                          <td className="spec-value">{val}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>

          {related.length > 0 && (
            <div className="related-products">
              <h3>Sản phẩm liên quan</h3>
              <div className="products-grid">
                {related.map((p) => (
                  <div key={p.id} className="product-card">
                    <Link to={`/product/${p.id}`} className="product-image">
                      <img src={p.image} alt={p.name} loading="lazy" />
                      <div className="product-overlay">
                        <span className="view-btn">Xem chi tiết</span>
                      </div>
                    </Link>
                    <div className="product-info">
                      <span className="product-category">{p.category}</span>
                      <h3>
                        <Link to={`/product/${p.id}`}>{p.name}</Link>
                      </h3>
                      <div className="product-price">
                        <span className="price-current">{formatPrice(p.price)}</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
