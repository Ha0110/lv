import { Link } from "react-router-dom";
import { formatPrice } from "../services/api";

export default function ProductCard({ product }) {
  return (
    <div className="product-card">
      <Link to={`/product/${product.id}`} className="product-image">
        <img src={product.image} alt={product.name} loading="lazy" />
        <div className="product-overlay">
          <span className="view-btn">Xem chi tiết</span>
        </div>
      </Link>
      <div className="product-info">
        <span className="product-category">{product.category}</span>
        <h3>
          <Link to={`/product/${product.id}`}>{product.name}</Link>
        </h3>
        <div className="product-price">
          <span className="price-current">{formatPrice(product.price)}</span>
          {product.oldPrice && (
            <span className="price-old">{formatPrice(product.oldPrice)}</span>
          )}
        </div>
        
      </div>
    </div>
  );
}
