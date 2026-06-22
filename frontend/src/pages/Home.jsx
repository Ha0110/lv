import { Link } from "react-router-dom";
import { useState, useEffect } from "react";
import ReactSlick from "react-slick";
const Slider = ReactSlick && ReactSlick.default ? ReactSlick.default : ReactSlick;
import ProductCard from "../components/ProductCard";
import { fetchProducts } from "../services/api";

const heroSlides = [
  {
    title: "Linh Kiện PC Cao Cấp",
    subtitle: "CPU - GPU - RAM - SSD",
    desc: "Hiệu suất đỉnh cao cho Gaming & Sáng tạo nội dung. Cam kết chính hãng 100%.",
    btn: "Mua ngay",
    link: "/products",
    bg: "https://images.pexels.com/photos/829455/pexels-photo-829455.jpeg?auto=compress&cs=tinysrgb&w=1920",
  },
  {
    title: "GPU RTX 40 Series",
    subtitle: "Ray Tracing & AI DLSS 3.0",
    desc: "Trải nghiệm gaming 4K mượt mà với công nghệ NVIDIA Ada Lovelace thế hệ mới.",
    btn: "Xem Card Đồ Họa",
    link: "/products?cat=VGA",
    bg: "https://images.pexels.com/photos/777001/pexels-photo-777001.jpeg?auto=compress&cs=tinysrgb&w=1920",
  },
  {
    title: "DDR5 & PCIe 5.0 SSD",
    subtitle: "Tốc Độ Vượt Trội",
    desc: "Nâng cấp hệ thống với RAM DDR5 và SSD NVMe Gen5 - tốc độ đọc lên đến 12,000 MB/s.",
    btn: "Khám phá",
    link: "/products?cat=RAM",
    bg: "https://images.pexels.com/photos/2580756/pexels-photo-2580756.jpeg?auto=compress&cs=tinysrgb&w=1920",
  },
];





export default function Home() {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    const loadProducts = async () => {
      const data = await fetchProducts();
      setProducts(data);
    };
    loadProducts();
  }, []);

  const featured = products.slice(0, 3);

  const sliderSettings = {
    dots: true,
    infinite: true,
    speed: 800,
    autoplay: true,
    autoplaySpeed: 5000,
    fade: true,
    arrows: true,
  };


  return (
    <>
      <section className="hero-slider">
        <Slider {...sliderSettings}>
          {heroSlides.map((slide, i) => (
            <div key={i} className="hero-slide" style={{ backgroundImage: `url(${slide.bg})` }}>
              <div className="hero-overlay" />
              <div className="container hero-content">
                <span className="hero-subtitle">{slide.subtitle}</span>
                <h1>{slide.title}</h1>
                <p>{slide.desc}</p>
                <Link to={slide.link} className="btn btn-primary">
                  {slide.btn}
                </Link>
              </div>
            </div>
          ))}
        </Slider>
      </section>



      <section className="section products-section">
        <div className="container">
          <div className="section-header">
            <h2>Sản phẩm nổi bật</h2>
            <p>Linh kiện PC cao cấp, hiệu suất vượt trội</p>
          </div>
          <div className="products-grid">
            {featured.length > 0 ? (
              featured.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))
            ) : (
              <p>Đang tải sản phẩm...</p>
            )}
          </div>
          <div className="text-center" style={{ marginTop: "2rem" }}>
            <Link to="/products" className="btn btn-outline">
              Xem tất cả sản phẩm
            </Link>
          </div>
        </div>
      </section>

      

      

      
    </>
  );
}
