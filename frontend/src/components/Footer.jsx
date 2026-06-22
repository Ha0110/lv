import { Link } from "react-router-dom";

export default function Footer() {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-grid">
          <div className="footer-col">
            <h3>
              <span className="brand-icon">⚡</span> HaSHop
            </h3>
            <p>
              Cửa hàng linh kiện máy tính 
            </p>
            <div className="social-links">
              <a href="#" aria-label="Facebook">f</a>
              <a href="#" aria-label="Twitter">t</a>
              <a href="#" aria-label="Instagram">i</a>
              <a href="#" aria-label="YouTube">y</a>
            </div>
          </div>



          <div className="footer-col">
            <h4>Thông tin</h4>
            <ul>
              <li><Link to="#">Về chúng tôi</Link></li>
              <li><Link to="#">Dịch vụ</Link></li>
              <li><Link to="#">Liên hệ</Link></li>
            </ul>
          </div>

          <div className="footer-col">
            <h4>Liên hệ</h4>
            <ul className="contact-list">
              <li>176 Cao Lỗ, Q.8, TP.HCM</li>
              <li>0909 123 456</li>
              <li>phamngocha785@gmail.com</li>
            </ul>
          </div>
        </div>

        <div className="footer-bottom">
          <p>&copy; 2026 HaShop.</p>
        </div>
      </div>
    </footer>
  );
}
